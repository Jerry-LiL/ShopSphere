<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        if (is_numeric($order)) {
            $order = Order::find($order);
        } elseif (!$order) {
            $id = $this->route('id');
            $order = $id ? Order::find($id) : null;
        }

        if (!$order) {
            return false;
        }

        return $this->user() && $this->user()->can('updateStatus', $order);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
            ])],
            'payment_status' => ['nullable', 'string', Rule::in([
                Order::PAYMENT_PENDING,
                Order::PAYMENT_PAID,
                Order::PAYMENT_FAILED,
                Order::PAYMENT_REFUNDED,
            ])],
        ];
    }
}
