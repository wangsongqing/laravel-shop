<?php

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function ngrok_url($routeName, $parameters = [])
{
    // 开发环境，并且配置了 NGROK_URL
    if(app()->environment('local') && $url = config('app.ngrok_url')) {
        // route() 函数第三个参数代表是否绝对路径
        return $url.route($routeName, $parameters, false);
    }

    return route($routeName, $parameters);
}

function object_to_array($obj) {

    $arr = (array)$obj;

    foreach ($arr as $k => $v) {

        if (gettype($v) == 'resource') {

            return;

        }

        if (gettype($v) == 'object' || gettype($v) == 'array') {

            $arr[$k] = (array)object_to_array($v);

        }

    }

    return $arr;
}

// 默认的精度为小数点后两位
function big_number($number, $scale = 2)
{
    return new \Moontoast\Math\BigNumber($number, $scale);
}
