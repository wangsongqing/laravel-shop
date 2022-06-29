<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaypalCallBack extends Model
{
    use HasFactory;
    protected $table = 'paypal_callback';
}
