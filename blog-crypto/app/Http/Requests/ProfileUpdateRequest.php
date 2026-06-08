<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],

        'username' => [
            'nullable',
            'string',
            'max:50',
            'alpha_dash',
            Rule::unique(User::class)->ignore($this->user()->id),
        ],

        'email' => [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            Rule::unique(User::class)->ignore($this->user()->id),
        ],

        'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

        'bio' => ['nullable', 'string', 'max:2000'],
        'headline' => ['nullable', 'string', 'max:160'],
        'occupation' => ['nullable', 'string', 'max:120'],
        'organization' => ['nullable', 'string', 'max:120'],
        'location' => ['nullable', 'string', 'max:120'],

        'website_url' => ['nullable', 'url', 'max:255'],
        'linkedin_url' => ['nullable', 'url', 'max:255'],
        'x_url' => ['nullable', 'url', 'max:255'],

        'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
        'expertise_areas' => ['nullable', 'array'],
        'expertise_areas.*' => ['string', 'max:80'],
    ];
}
}
