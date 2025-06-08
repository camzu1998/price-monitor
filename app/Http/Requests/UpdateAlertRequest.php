<?php

namespace App\Http\Requests;

use App\Enums\AlertCondition;
use App\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_price' => 'sometimes|numeric|min:0|max:999999.99',
            'percent_threshold' => 'sometimes|numeric|min:0.01|max:100',
            'is_active' => 'sometimes|boolean',
            'notification_channel' => ['sometimes', Rule::in(NotificationChannel::getValues())],
            'condition' => ['sometimes', Rule::in(AlertCondition::getValues())],
        ];
    }

    public function messages(): array
    {
        return [
            'target_price.numeric' => 'Target price must be a valid number.',
            'target_price.min' => 'Target price must be greater than 0.',
            'percent_threshold.numeric' => 'Percentage threshold must be a valid number.',
            'percent_threshold.min' => 'Percentage threshold must be at least 0.01%.',
            'percent_threshold.max' => 'Percentage threshold cannot exceed 100%.',
            'is_active.boolean' => 'Active status must be true or false.',
            'notification_channel.in' => 'Invalid notification method selected.',
            'condition.in' => 'Invalid alert condition selected.',
        ];
    }
}
