<?php

declare(strict_types=1);

namespace App\Module\Transaction\Presentation\Request;

use Hyperf\Validation\Request\FormRequest;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0.01',
            'payer' => 'required|exists:users,id',
            'payee' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'The value is required.',
            'value.numeric' => 'The value must be a number.',
            'value.min' => 'The value must be greater than 0.01.',
            'payer.required' => 'The payer is required.',
            'payer.exists' => 'The payer does not exist.',
            'payee.required' => 'The payee is required.',
            'payee.exists' => 'The payee does not exist.',
        ];
    }
}
