<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerLoginRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'senha' => 'required|min:6',
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required' => 'O campo de email é obrigatório.',
            'email.email' => 'Insira um email válido.',
            'senha.required' => 'O campo de senha é obrigatório.',
            'senha.min' => 'A senha deve ter pelo menos :min caracteres.',
        ];
    }
}
