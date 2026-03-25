<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Compte utilisateur ─────────────────────────
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],

            // ── Entreprise ─────────────────────────────────
            'company_name' => ['required', 'string', 'max:100'],
            'sector'       => ['required', 'string', 'in:restaurant,boutique,quincaillerie,location,autre'],
            'phone'        => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Votre nom est obligatoire.',
            'email.required'        => 'L\'adresse e-mail est obligatoire.',
            'email.email'           => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique'          => 'Cet e-mail est déjà utilisé.',
            'password.required'     => 'Le mot de passe est obligatoire.',
            'password.confirmed'    => 'Les mots de passe ne correspondent pas.',
            'company_name.required' => 'Le nom de votre entreprise est obligatoire.',
            'sector.required'       => 'Choisissez votre secteur d\'activité.',
            'sector.in'             => 'Secteur invalide.',
        ];
    }
}
