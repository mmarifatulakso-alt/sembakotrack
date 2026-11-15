<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WatchlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi kepemilikan ditangani Policy di controller
    }

    // Sanitasi catatan: buang tag HTML (output tetap di-escape Blade sebagai lapis kedua anti-XSS)
    protected function prepareForValidation(): void
    {
        if ($this->has('catatan')) {
            $clean = trim(strip_tags((string) $this->input('catatan')));
            $this->merge(['catatan' => $clean === '' ? null : $clean]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'target_harga' => ['required', 'numeric', 'min:100', 'max:100000000'],
            'catatan'      => ['nullable', 'string', 'max:255'],
        ];

        // komoditas_id hanya diterima saat MENAMBAH (POST), tidak bisa diubah saat update.
        if ($this->isMethod('post')) {
            $rules['komoditas_id'] = [
                'required', 'integer',
                Rule::exists('komoditas', 'id'),
                Rule::unique('watchlists', 'komoditas_id')->where('user_id', $this->user()->id),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return ['komoditas_id.unique' => 'Komoditas ini sudah ada di daftar pantauan Anda.'];
    }
}