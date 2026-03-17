<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'expense_date' => 'required|date',
                'note' => 'nullable|string',
            ];
        }

        return [
            'title' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0.01',
            'expense_date' => 'nullable|date',
            'note' => 'nullable|string',
        ];
    }
}
