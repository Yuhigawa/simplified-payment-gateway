<?php

declare(strict_types=1);

namespace App\Module\Account\Presentation\Request;

use App\Module\Account\Domain\ValueObject\DocumentType;
use Hyperf\Validation\Request\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        // TODO: properly validate document with it's type
        return [
            'name' => 'required|string|max:200',
            'email' => 'required|string|email|max:200|unique:users,email',
            'password' => 'required|string|max:200',
            'document' => 'required|string|max:14|unique:users,document',
            'document_type' => 'required|string|in:' . implode(',', DocumentType::getValues()),
            'balance' => 'required|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name may not be greater than 200 characters.',

            'email.required' => 'The email is required.',
            'email.string' => 'The email must be a valid string.',
            'email.max' => 'The email may not be greater than 200 characters.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a valid string.',
            'password.max' => 'The password may not be greater than 200 characters.',

            'document.required' => 'The document is required.',
            'document.string' => 'The document must be a valid string.',
            'document.max' => 'The document may not be greater than 14 characters.',
            'document.unique' => 'This document is already registered.',

            'document_type.required' => 'The document type is required.',
            'document_type.string' => 'The document type must be a valid string.',
            'document_type.in' => 'The selected document type is invalid.',
        ];
    }
}
