<?php
namespace App\Jobs;

use App\Exceptions\InternalException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PayPal\Api\Amount;
use PayPal\Api\Payment;
use PayPal\Api\Refund;
use PayPal\Api\Sale;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

// ShouldQueue 代表这是一个异步任务
class RefundInstallmentOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * @var ApiContext
     */
    private $PayPal;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->PayPal = new ApiContext(
            new OAuthTokenCredential(
                config('payment.paypal.client_id'),
                config('payment.paypal.secret'),
            )
        );
    }

    public function handle()
    {
        // 如果商品订单支付方式不是分期付款、订单未支付、订单退款状态不是退款中，则不执行后面的逻辑
        if ($this->order->payment_method !== 'installment'
            || !$this->order->paid_at
            || $this->order->refund_status !== Order::REFUND_STATUS_PROCESSING) {
            return;
        }
        // 找不到对应的分期付款，原则上不可能出现这种情况，这里的判断只是增加代码健壮性
        if (!$installment = Installment::query()->where('order_id', $this->order->id)->first()) {
            return;
        }
        // 遍历对应分期付款的所有还款计划
        foreach ($installment->items as $item) {
            // 如果还款计划未支付，或者退款状态为退款成功或退款中，则跳过
            if (!$item->paid_at || in_array($item->refund_status, [
                    InstallmentItem::REFUND_STATUS_SUCCESS,
                    InstallmentItem::REFUND_STATUS_PROCESSING,
                ])) {
                continue;
            }
            // 调用具体的退款逻辑，
            try {
                $this->refundInstallmentItem($item);
            } catch (\Exception $e) {
                \Log::warning('分期退款失败：'.$e->getMessage(), [
                    'installment_item_id' => $item->id,
                ]);
                // 假如某个还款计划退款报错了，则暂时跳过，继续处理下一个还款计划的退款
                continue;
            }
        }
        // 设定一个全部退款成功的标志位
        $allSuccess = true;
        // 再次遍历所有还款计划
        foreach ($installment->items as $item) {
            // 如果该还款计划已经还款，但退款状态不是成功
            if ($item->paid_at &&
                $item->refund_status !== InstallmentItem::REFUND_STATUS_SUCCESS) {
                // 则将标志位记为 false
                $allSuccess = false;
                break;
            }
        }
        // 如果所有退款都成功，则将对应商品订单的退款状态修改为退款成功
        if ($allSuccess) {
            $this->order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        }
    }

    protected function refundInstallmentItem(InstallmentItem $item)
    {
        // 判断该订单的支付方式
        switch ($item->payment_method) {
            case 'wechat':
                // 微信的先留空
                // todo
                break;
            case 'alipay':
                // 支付宝退款
                break;
            case 'paypal':
                // PayPal 退款
                // 用我们刚刚写的方法来生成一个退款订单号
                $refundNo = Order::getAvailableRefundNo();

                // 将订单的退款状态标记为退款成功并保存退款订单号
                try {
                    $payment = Payment::get($item->payment_no, $this->PayPal);
                    $a = $payment->transactions;
                    $txn_id = '';
                    foreach ($a as $v) {
                        foreach ($v->related_resources as $k) {
                            if (isset($k->sale)) {
                                $txn_id = $k->sale->id ?? '';
                            }
                        }
                    }

                    if (!$txn_id) {
                        throw new \Exception('退款失败', -1);
                    }

                    $amt    = new Amount();

                    $amt->setCurrency('USD')->setTotal($item->base);  // 退款的费用

                    $refund = new Refund();
                    $refund->setAmount($amt);

                    $sale = new Sale();
                    $sale->setId($txn_id);

                    $refundedSale = $sale->refund($refund, $this->PayPal);

                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
                    ]);
                    // print_r('refundedSale:'.$refundedSale);

                } catch (\Exception $e) {
                    // print_r('Message:'.$e->getMessage());
                    // PayPal无效退款
                }
                break;
            default:
                // 原则上不可能出现，这个只是为了代码健壮性
                throw new InternalException('未知订单支付方式：' . $item->payment_method);
                break;
        }
    }
}
