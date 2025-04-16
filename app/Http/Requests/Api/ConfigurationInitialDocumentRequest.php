<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ConfigurationInitialDocumentRequest extends FormRequest
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('type_document_id')) {
            $value = $this->input('type_document_id');

            // Buscar por id o por code
            $record = DB::table('type_documents')
                ->where('id', $value)
                ->orWhere('code', $value)
                ->first();

            if ($record) {
                // Reemplazar con el id real
                $this->merge([
                    'type_document_id' => $record->id,
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'identification_number' => 'required|numeric|digits_between:1,15',
            'type_document_id' => 'required|exists:type_documents,id',
            'prefix' => 'nullable|string|max:4',
            'number' => 'required|integer',
        ];
    }
}
