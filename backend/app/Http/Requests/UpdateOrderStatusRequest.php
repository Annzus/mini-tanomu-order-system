<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(Order::STATUSES)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $order = $this->route('order');
                $status = $this->input('status');

                if (! $order instanceof Order || ! is_string($status)) {
                    return;
                }

                if (! in_array($status, $this->allowedNextStatuses($order->status), true)) {
                    $validator->errors()->add('status', 'Invalid status transition.');
                }
            },
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allowedNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            Order::STATUS_PENDING => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
            Order::STATUS_CONFIRMED => [Order::STATUS_PREPARING, Order::STATUS_CANCELLED],
            Order::STATUS_PREPARING => [Order::STATUS_DELIVERED],
            default => [],
        };
    }
}
