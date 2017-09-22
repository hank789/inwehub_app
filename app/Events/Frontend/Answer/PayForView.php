<?php namespace App\Events\Frontend\Answer;
use App\Models\Pay\Order;
use Illuminate\Queue\SerializesModels;

/**
 * 服务围观
 */
class PayForView
{

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
