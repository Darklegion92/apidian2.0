<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RutRequest extends FormRequest
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
            'identification_number' => 'required|numeric',
            'type_document_id' => 'nullable|integer|exists:type_document_identifications,id',
        ];
    }

    public function messages()
    {
        return [
            'identification_number.required' => 'El número de identificación es obligatorio',
            'identification_number.numeric' => 'El número de identificación debe ser numérico y/o alfanumérico',
            'type_document_id.integer' => 'El número de tipo de documento debe ser un entero',
            'type_document_id.required' => 'El número de tipo de documento es obligatorio',
            'type_document_id.exists' => 'El número de tipo de documento seleccionado no existe.',
            'rues.boolean' => 'La propiedad rues debe ser verdadero o false',
        ];
    }
}
