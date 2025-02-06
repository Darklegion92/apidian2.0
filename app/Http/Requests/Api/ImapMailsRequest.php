<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ImapMailsRequest extends FormRequest
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
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'last_event' => 'nullable|integer|in:0,1,3',
            'base64_attacheddocument' => 'nullable|boolean',
            'only_unread' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'start_date.required' => 'El campo fecha inicial es obligatorio.',
            'start_date.date_format' => 'La fecha inicial debe estar en el formato YYYY-MM-DD',
            'end_date.date_format' => 'La fecha final debe estar en el formato YYYY-MM-DD',
            'end_date.after_or_equal' => 'La fecha final debe ser mayor o igual que la fecha inicial, start_date',
            'last_event.exists' => 'El ID debe existir en la lista de eventos.',
            'last_event.in' => 'Error, last_event debe ser igual a 0, 1 o 3, (0 - No Ejecutar ningun evento, 1 - Solo acuse de recibo, 3 - Acuse de recibo y recepcion de bienes).',
            'base64_attacheddocument.boolean' => 'Debe ser un booleano con true o false',
            'only_unread.boolean' => 'El valor de la propiedad only_unread debe ser  un booleano con true o false',
        ];
    }

}
