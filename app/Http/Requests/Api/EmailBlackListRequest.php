<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EmailBlackListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'emails' => 'required|array', // Debe ser un array
            'emails.*.email' => 'required|string|email', // Cada elemento debe tener un email válido
            'emails.*.banned' => 'nullable|boolean', // 'banned' es opcional, pero si se envía debe ser booleano
        ];
    }

    protected function prepareForValidation()
    {
        $emails = $this->input('emails', []);

        // Si 'banned' no está presente en algún objeto, lo establece en true
        foreach ($emails as &$email) {
            if (!isset($email['banned'])) {
                $email['banned'] = true;
            }
        }

        $this->merge([
            'emails' => $emails
        ]);
    }
}
