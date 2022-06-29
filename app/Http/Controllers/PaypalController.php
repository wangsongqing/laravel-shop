<?php

namespace App\Http\Controllers;


use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\PaypalCallBack;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use PayPal\Api\PaymentExecution;


class PaypalController extends Controller
{
    const accept_url = 'http://laravel-shop.org/paypal/callback'; //支付成功和取消交易的跳转地址
    const Currency = 'USD';//货币单位

    protected $PayPal;

    public function __construct()
    {
        $this->PayPal = new ApiContext(
            new OAuthTokenCredential(
                config('payment.paypal.client_id'),
                config('payment.paypal.secret'),
            )
        );
        //如果是沙盒测试环境不设置，请注释掉
//        $this->PayPal->setConfig(
//            array(
//                'mode' => 'live',
//            )
//        );
    }

    /**
     * @param
     * $product 商品
     * $price 价钱
     * $shipping 运费
     * $description 描述内容
     */
    public function pay(Order $order)
    {
        // 判断订单是否属于当前用户
        $this->authorize('own', $order);
        $orderItem   = $order->items()->where('order_id', $order->id)->first();
        $products    = Product::query()->where('id', $orderItem->product_id)->first();
        $productsSku = ProductSku::query()->where('id', $orderItem->product_sku_id)->first();
        $product     = $products->title . ' ' . $productsSku->title;
        $price       = $order->total_amount;
        $shipping    = 0;
        $description = $products->description;
        $paypal      = $this->PayPal;
        $total       = $price + $shipping;//总价

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
        $redirectUrls->setReturnUrl(self::accept_url . '?success=true&order_id=' . $order->no)->setCancelUrl(self::accept_url . '/?success=false&order_id=' . $order->no);

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

    /**
     * 回调
     */
    public function callback(Request $request)
    {
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

        $payment = Payment::get($paymentId, $this->PayPal);
        $execute = new PaymentExecution();
        $execute->setPayerId($PayerID);
        try {
            $payment->execute($execute, $this->PayPal);
        } catch (\Exception $e) {
            echo ',支付失败，支付ID【' . $paymentId . '】,支付人ID【' . $PayerID . '】';
            die;
        }

        $order = Order::where('no', $request->order_id)->first();
        $order->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'paypal', // 支付方式
            'payment_no'     => $paymentId, // PayPal订单号
            'payer_id'       => $PayerID
        ]);
        $order->save();

        // 添加事件
        event(new OrderPaid($order));

        $url = 'http://laravel-shop.org/orders/' . $order->id;
        header("Location: {$url}");
    }


    public function callbackPaypal(Request $request)
    {
        $payment = Payment::get('PAYID-MK2TGWA1TM19766NJ991174M', $this->PayPal, null, false);
        echo '<pre>';
        // print_r(object_to_array($payment));
        $a = $payment->transactions;
        $id = '';
       foreach ($a as $v) {
           foreach ($v->related_resources as $k) {
               if (isset($k->sale)) {
                   $id = $k->sale->id ?? '';
               }
           }
       }

       var_dump($id);
        exit();
        $time = date('Y-m-d H:i:s');
        $data = [
            'callback_request'=> json_encode($request->all()),
            'created_at' => $time,
            'updated_at' => $time
        ];

        $lastId = PaypalCallBack::query()->insertGetId($data);
        return response()->json([
            'code'    => 1,
            'message' => 'success'
        ]);
    }
}
