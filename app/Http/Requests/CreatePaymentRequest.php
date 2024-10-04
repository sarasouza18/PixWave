<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta solicitação.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Regras de validação para a solicitação de criação de pagamento.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
        ];
    }

    /**
     * Mensagens personalizadas para os erros de validação.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => 'O campo de ID do usuário é obrigatório.',
            'user_id.exists' => 'O usuário fornecido não existe.',
            'amount.required' => 'O campo de valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser numérico.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'currency.max' => 'A moeda deve ter no máximo 3 caracteres.',
        ];
    }
}
