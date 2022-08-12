<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function own(User $user, Order $order)
    {
        return $order->user_id == $user->id;
    }

    public function view(User $user, Order $model)
    {
        return true;
    }

    public function update(User $user, Order $model)
    {
        return true;
    }
}
