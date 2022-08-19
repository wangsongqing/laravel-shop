<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissions extends \Spatie\Permission\Models\Permission
{
    // 权限
    const UPDATE_ORDER_SKU = 'update_order_sku';
    const UN_DOWNLOAD = 'un_download';
    const DOWNLOADED = 'downloaded';
    const DOWNLOAD_FAILED = 'download_failed';
    const PROCESSING = 'processing';
    const PROCESSED = 'processed';
    const PUBLISHED = 'published';
    const PUBLISHING = 'publishing';
    const PUBLISH_FAILED = 'publish_failed';
    const CONFIRMED = 'confirmed';
    const PRODUCED = 'produced';
    const FROZEN = 'frozen';
    const WAIT_CHANGE = 'wait_change';
    const CANCEL = 'cancel';
    const SEND = 'send';
    const CONFIG = 'config';
    const URGENT =  'urgent'; // 订单加急
    const COPY_FILE = 'copy_file'; // 复制文件

    const PERMISSIONS = [
        self::UPDATE_ORDER_SKU => '修改订单SKU',
        self::UN_DOWNLOAD      => '未下载',
        self::DOWNLOADED       => '已下载',
        self::DOWNLOAD_FAILED  => '下载失败',
        self::PROCESSING       => '处理中',
        self::PROCESSED        => '处理完成',
        self::PUBLISHED        => '已发稿',
        self::PUBLISHING       => '发稿中',
        self::PUBLISH_FAILED   => '发稿失败',
        self::CONFIRMED        => '已确认',
        self::PRODUCED         => '已生产',
        self::FROZEN           => '已冻结',
        self::WAIT_CHANGE      => '待修改',
        self::CANCEL           => '取消',
        self::SEND             => '已发货',
        self::CONFIG           => '配置修改',
        self::URGENT           => '订单加急',
        self::COPY_FILE        => '复制文件',
    ];

    protected $appends = [
        'title_name',
    ];

    public function getTitleNameAttribute() {
        return self::PERMISSIONS[$this->name] ?? $this->name;
    }

}
