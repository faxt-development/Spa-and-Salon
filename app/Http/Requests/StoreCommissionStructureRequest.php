<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommissionStructureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('manage commission_structures');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,flat,tiered',
            'default_rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
            'rules' => 'required_if:type,tiered|array',
            'rules.*.name' => 'required_with:rules|string|max:255',
            'rules.*.description' => 'nullable|string',
            'rules.*.condition_type' => 'required_with:rules|string|in:sales_volume,item_count',
            'rules.*.min_value' => 'required_with:rules|numeric|min:0',
            'rules.*.max_value' => 'nullable|numeric|gt:rules.*.min_value',
            'rules.*.rate' => 'required_with:rules|numeric|min:0|max:100',
            'rules.*.is_active' => 'sometimes|boolean',
            'rules.*.priority' => 'required_with:rules|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rules.required_if' => 'Commission rules are required for tiered commission structures.',
            'rules.*.min_value.required_with' => 'The min value is required for each rule.',
            'rules.*.rate.required_with' => 'The rate is required for each rule.',
            'rules.*.priority.required_with' => 'The priority is required for each rule.',
        ];
    }
}
