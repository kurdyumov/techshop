<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // dd([
        //     'logged_in'=>Auth::check(),
        //     'is_employer'=>(bool)Auth::user()->getTypes()->is_employer,
        //     'is_admin'=>array_key_exists(0, Auth::user()->getRoles())
        // ]);
        return Auth::check() && Auth::user()->getTypes()->is_employer && array_key_exists(0, Auth::user()->getRoles());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
