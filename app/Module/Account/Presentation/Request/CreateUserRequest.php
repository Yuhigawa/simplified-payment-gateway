<?php

declare(strict_types=1);

namespace App\Module\Account\Presentation\Request;

use App\Module\Account\Domain\ValueObject\DocumentType;
use Hyperf\Validation\Request\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
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
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 200 caracteres.',
            
            'email.required' => 'O e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser um texto válido.',
            'email.max' => 'O e-mail não pode ter mais de 200 caracteres.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser um texto válido.',
            'password.max' => 'A senha não pode ter mais de 200 caracteres.',
            
            'document.required' => 'O documento é obrigatório.',
            'document.string' => 'O documento deve ser um texto válido.',
            'document.max' => 'O documento não pode ter mais de 14 caracteres.',
            'document.unique' => 'Este documento já está cadastrado.',
            
            'document_type.required' => 'O tipo de documento é obrigatório.',
            'document_type.string' => 'O tipo de documento deve ser um texto válido.',
            'document_type.in' => 'O tipo de documento selecionado é inválido.',
        ];
    }
}
