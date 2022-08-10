<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class CouponCodes extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\CouponCode::class;

    public static $group = '优惠券管理';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = '优惠券管理';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title',
    ];

    public static function label() {
        return '优惠券管理';
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

            Text::make('名称', 'name')->rules('required'),
            Text::make('优惠码', 'code')->rules('required'),
            Text::make('描述', function ($model) {
                return $model->getDescriptionAttribute() ?? '';
            }),
            Text::make('用量', function($model) {
                return "{$model->used} / {$model->total}";
            }),

            Select::make('是否启用', 'enabled')
                ->options(\App\Models\CouponCode::ENABLE)
                ->displayUsingLabels()
                ->rules('required', 'max:255'),

            Select::make('类型', 'type')
                ->options(\App\Models\CouponCode::$typeMap)
                ->displayUsingLabels()
                ->rules('required', 'max:255'),
            Text::make('折扣', 'value')->rules('required'),
            Text::make('最低金额', 'min_amount')->rules('required'),
            DateTime::make('开始时间', 'not_before')->rules('required'),
            DateTime::make('结束时间', 'not_after')->rules('required'),
            DateTime::make('创建时间', 'created_at')
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
}
