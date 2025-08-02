<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $settingId = $this->route('setting');

        return [
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('app_settings', 'key')->ignore($settingId),
            ],
            'value' => 'nullable|string|max:65535',
            'type' => ['required', Rule::in(['text', 'image', 'file', 'boolean', 'number'])],
            'group' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,ico,svg|max:2048'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Kunci pengaturan harus diisi.',
            'key.unique' => 'Kunci pengaturan sudah ada.',
            'type.in' => 'Tipe pengaturan tidak valid.',
            'label.required' => 'Label pengaturan harus diisi.',
            'file.mimes' => 'File harus berformat JPG, PNG, GIF, ICO, atau SVG.',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'key' => 'kunci pengaturan',
            'value' => 'nilai pengaturan',
            'type' => 'tipe pengaturan',
            'group' => 'grup pengaturan',
            'label' => 'label pengaturan',
            'description' => 'deskripsi pengaturan',
            'file' => 'file',
        ];
    }
}
