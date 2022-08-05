<?php
namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class InstallmentsController extends Controller
{
    const Currency = 'USD';//货币单位

    protected $PayPal;

    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return view('installments.index', ['installments' => $installments]);
    }

    public function show(Installment $installment)
    {
        $this->authorize('own', $installment);
        // 取出当前分期付款的所有的还款计划，并按还款顺序排序
        $items = $installment->items()->orderBy('sequence')->get();
        return view('installments.show', [
            'installment' => $installment,
            'items'       => $items,
            // 下一个未完成还款的还款计划
            'nextItem'    => $items->where('paid_at', null)->first(),
        ]);
    }

    public function payByPayPal(Installment $installment)
    {
        $this->authorize('own', $installment);
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已被关闭');
        }

        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }

        // 获取当前分期付款最近的一个未支付的还款计划
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            // 如果没有未支付的还款，原则上不可能，因为如果分期已结清则在上一个判断就退出了
            throw new InvalidRequestException('该分期订单已结清');
        }

        $this->PayPal = new ApiContext(
            new OAuthTokenCredential(
                config('payment.paypal.client_id'),
                config('payment.paypal.secret'),
            )
        );

        $products = OrderItem::query()->where('order_id',$installment->order->id)->first();

        // $InstallmentItem = InstallmentItem::query()->where('installment_id', $installment->id)->first();
        $InstallmentItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first();
        $product     = $products->product->title . ' ' . $products->productSku->title;
        $price       = $InstallmentItem->total;
        $shipping    = 0;
        $description = $products->description;
        $paypal      = $this->PayPal;
        $total       = $InstallmentItem->total;//总价

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($product)->setCurrency(self::Currency)->setQuantity(1)->setPrice($price);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping($shipping)->setSubtotal($price);

        $amount = new Amount();
        $amount->setCurrency(self::Currency)->setTotal($total)->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)->setDescription($description)->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(config('pay.paypal.callback_installments') . '?success=true&out_trade_no=' . $installment->no. '_'. $InstallmentItem->sequence . '&installments_id=' . $installment->id)->setCancelUrl(config('pay.paypal.callback_installments') . '/?success=false&order_id=' . $installment->no. '_'. $InstallmentItem->sequence . '&installments_id=' . $installment->id);

        $payment = new Payment();
        $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions([$transaction]);

        try {
            $payment->create($paypal);
        } catch (PayPalConnectionException $e) {
            echo $e->getData();
            die();
        }

        $approvalUrl = $payment->getApprovalLink();
        header("Location: {$approvalUrl}");
    }

    public function installmentsCallback() {
        $success = trim($_GET['success']);
        if ($success == 'false' && !isset($_GET['paymentId']) && !isset($_GET['PayerID'])) {
            echo '取消付款';
            die;
        }
        $paymentId = trim($_GET['paymentId']);
        $PayerID   = trim($_GET['PayerID']);
        if (!isset($success, $paymentId, $PayerID)) {
            echo '支付失败';
            die;
        }
        if ((bool)$_GET['success'] === 'false') {
            echo '支付失败，支付ID【' . $paymentId . '】,支付人ID【' . $PayerID . '】';
            die;
        }

        $installmentsId = $_GET['installments_id'];
        $this->PayPal = new ApiContext(
            new OAuthTokenCredential(
                config('payment.paypal.client_id'),
                config('payment.paypal.secret'),
            )
        );
        $payment = Payment::get($paymentId, $this->PayPal);
        $execute = new PaymentExecution();
        $execute->setPayerId($PayerID);
        try {
            $payment->execute($execute, $this->PayPal);
        } catch (\Exception $e) {
            echo ',支付失败，支付ID【' . $paymentId . '】,支付人ID【' . $PayerID . '】';
            die;
        }

        // 拉起支付时使用的支付订单号是由分期流水号 + 还款计划编号组成的
        // 因此可以通过支付订单号来还原出这笔还款是哪个分期付款的哪个还款计划
        list($no, $sequence) = explode('_', $_GET['out_trade_no']);
        // 根据分期流水号查询对应的分期记录，原则上不会找不到，这里的判断只是增强代码健壮性
        if (!$installment = Installment::where('no', $no)->first()) {
            die('fail');
        }
        // 根据还款计划编号查询对应的还款计划，原则上不会找不到，这里的判断只是增强代码健壮性
        if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
            die('fail');
        }
        // 如果这个还款计划的支付状态是已支付，则告知支付宝此订单已完成，并不再执行后续逻辑
        if ($item->paid_at) {
            $url = env('APP_URL').'/installments/' . $installmentsId;
            header("Location: {$url}");
        }

        // 使用事务，保证数据一致性
        \DB::transaction(function () use ($no, $installment, $item, $paymentId) {
            // 更新对应的还款计划
            $item->update([
                'paid_at'        => Carbon::now(), // 支付时间
                'payment_method' => 'paypal', // 支付方式
                'payment_no'     => $paymentId, // 支付宝订单号
            ]);

            // 如果这是第一笔还款
            if ($item->sequence === 0) {
                // 将分期付款的状态改为还款中
                $installment->update(['status' => Installment::STATUS_REPAYING]);
                // 将分期付款对应的商品订单状态改为已支付
                $installment->order->update([
                    'paid_at'        => Carbon::now(),
                    'payment_method' => 'installment', // 支付方式为分期付款
                    'payment_no'     => $no, // 支付订单号为分期付款的流水号
                ]);
                // 触发商品订单已支付的事件
                event(new OrderPaid($installment->order));
            }

            // 如果这是最后一笔还款
            if ($item->sequence === $installment->count - 1) {
                // 将分期付款状态改为已结清
                $installment->update(['status' => Installment::STATUS_FINISHED]);
            }
        });

        $url = env('APP_URL').'/installments/' . $installmentsId;
        header("Location: {$url}");
    }
}
