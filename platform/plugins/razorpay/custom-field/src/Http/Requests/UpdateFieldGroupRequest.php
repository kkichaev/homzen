<?php

namespace Botble\CustomField\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateFieldGroupRequest extends Request
{
    public function rules(): array
    {
        return [
            'order' => ['required', 'integer', 'min:0', 'max:127'],
            'rules' => ['json', 'required'],
            'group_items' => ['json', 'required'],
            'deleted_items' => ['json', 'nullable'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(BaseStatusEnum::values())],
        ];
    }
}
