<?php

namespace App\Nova;

use App\Models\Category;
use AwesomeNova\Filters\DependentFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class ProductSeckill extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Product::class;

    public static $group = '商品管理';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = '普通管理';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title',
    ];

    public static function label() {
        return '秒杀商品管理';
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

            Text::make('商品名称', 'title')
                ->sortable()
                ->rules('required', 'max:255'),
            Text::make('商品长标题', 'long_title')
                ->sortable()
                ->rules('required', 'max:255'),
            Textarea::make('商品描述','description')->rules('required', 'max:255'),


            Text::make('类目', function ($model) {
                $categoryName = Category::query()->where('id', $model->category_id)->first();
                return $categoryName->name ?? '';
            }),

            Select::make('是否上架','on_sale')
                ->options(\App\Models\Product::ON_SALE)
                ->displayUsingLabels()->rules('required', 'max:255'),

            Select::make('商品类型','type')
                ->options(\App\Models\Product::$typeMap)
                ->displayUsingLabels()->rules('required', 'max:255'),

            Text::make('价格', 'price')->rules('required', 'max:255'),
//            Number::make('评分', 'rating'),
//            Number::make('销量', 'sold_count'),
//            Number::make('评论数', 'review_count'),
            Image::make('封面图片', 'image'),
            HasOne::make('秒杀时间', 'seckill', 'App\Nova\SeckillProducts'),
            HasMany::make('商品sku', 'skus', 'App\Nova\ProductSku'),
            HasMany::make('商品属性', 'properties', 'App\Nova\ProductProperties')
            // Image::make('描述', 'description'),
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
            DependentFilter::make('商品类型', 'type')
                ->withOptions(\App\Models\Product::$typeMap)
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
            ->where('type', 'seckill');
    }
}
