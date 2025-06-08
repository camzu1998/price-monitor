<?php

namespace App\Http\Requests;

use App\Enums\AlertCondition;
use App\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'email' => 'required|email|max:255',
            'condition' => ['required', Rule::in(AlertCondition::getValues())],
            'target_price' => 'required_if:condition,below,above,equals|numeric|min:0|max:999999.99',
            'percent_threshold' => 'required_if:condition,percent_drop,percent_increase|numeric|min:0.01|max:100',
            'notification_channel' => ['required', Rule::in(NotificationChannel::getValues())],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product selection is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'condition.required' => 'Alert condition is required.',
            'condition.in' => 'Invalid alert condition selected.',
            'target_price.required_if' => 'Target price is required for price-based alerts.',
            'target_price.numeric' => 'Target price must be a valid number.',
            'target_price.min' => 'Target price must be greater than 0.',
            'percent_threshold.required_if' => 'Percentage threshold is required for percentage-based alerts.',
            'percent_threshold.min' => 'Percentage threshold must be at least 0.01%.',
            'percent_threshold.max' => 'Percentage threshold cannot exceed 100%.',
            'notification_channel.required' => 'Notification method is required.',
            'notification_channel.in' => 'Invalid notification method selected.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower($this->email),
            ]);
        }

        if ($this->has('condition')) {
            $condition = AlertCondition::tryFrom($this->condition);
            if ($condition && $condition->isPercentBasedCondition() && !$this->has('target_price')) {
                $this->merge(['target_price' => 0]);
            }
        }
    }
}
