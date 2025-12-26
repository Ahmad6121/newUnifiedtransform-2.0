<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoticeStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // أو خليها حسب صلاحياتك
    }

    public function rules()
    {
        return [
            'session_id'      => ['required', 'integer'],
            'notice'          => ['required', 'string'],
            'audience_type'   => ['required', 'in:all,roles,users'],

            'audience_roles'   => ['nullable', 'array', 'required_if:audience_type,roles', 'min:1'],
            'audience_roles.*' => ['string'],

            'audience_users'  => ['nullable', 'string', 'required_if:audience_type,users'],
        ];
    }
}
