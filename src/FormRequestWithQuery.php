<?php

namespace ElMag\RQ;

use Illuminate\Foundation\Http\FormRequest;

class FormRequestWithQuery extends FormRequest
{
    use FormRequestQuery;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}
