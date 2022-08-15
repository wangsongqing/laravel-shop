<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;

class OrdersRefund extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Order::class;

    public static $group = '订单管理';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = '订单退款';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'no',
    ];

    public static function label() {
        return '订单退款';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('订单流水号', 'no'),
            Text::make('买家', function($model) {
                $userInfo = \App\Models\User::query()->where('id', $model->user_id)->first();
                return $userInfo->name ?? '';
            }),
            Text::make('总金额', 'total_amount'),
            DateTime::make('支付时间', 'paid_at'),

            Select::make('物流','ship_status')
                ->options(\App\Models\Order::$shipStatusMap)
                ->displayUsingLabels()->rules('required', 'max:255'),

            Select::make('退款状态','refund_status')
                ->options(\App\Models\Order::$refundStatus)
                ->displayUsingLabels()->rules('required', 'max:255'),

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    public static function indexQuery(NovaRequest $request, $query) {
        return $query
            ->where('refund_status', 'applied')->whereNull('refund_no');
    }
}
