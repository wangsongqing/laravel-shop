<?php

namespace App\Nova;

use App\Models\AdminPermission;
use App\Models\AdminRolePermission;
use App\Permission;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Vyuldashev\NovaPermission\PermissionBooleanGroup;
use Vyuldashev\NovaPermission\Role;
use Vyuldashev\NovaMoneyField\Money;
class AdminRole extends Role
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\AdminRole::class;

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
        'username', 'name',
    ];

    public static function label() {
        return '角色';
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

            Text::make('名称', 'name')
                ->rules(['required', 'string', 'max:255']),

            Select::make('权限', function ($model) {
                $roleId = AdminRolePermission::query()->where('role_id', $model->id)->get()->toArray();
                $arr = array_column($roleId, 'permission_id');
                $per = AdminPermission::query()->whereIn('id', $arr)->get()->toArray();
                return implode(',' ,array_column($per, 'name'));
            })
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
