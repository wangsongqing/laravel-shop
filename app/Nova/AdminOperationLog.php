<?php

namespace App\Nova;

use App\Models\AdminPermission;
use App\Permission;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Vyuldashev\NovaPermission\PermissionBooleanGroup;

class AdminOperationLog extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\AdminOperationLog::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'username';

    public static $group = '系统管理';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'user_id', 'path','ip'
    ];

    public static function label() {
        return '操作日志';
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

            Text::make('用户', function($model){
                $userInfo = \App\Models\AdminUser::query()->find($model->user_id);
                return $userInfo->username;
            })->rules(['required', 'string', 'max:255']),

            Text::make('路径', 'path')
                ->rules(['required', 'string', 'max:255']),

            Text::make('IP', 'ip')
                ->rules(['required', 'string', 'max:255'])


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
        return [];
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
