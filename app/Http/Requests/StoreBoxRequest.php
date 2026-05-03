<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    return [
        'name'                       => 'required|string',
        'price'                      => 'required|numeric',
        'description'                => 'nullable|string',
        'store_id'                   => 'nullable|string',
        'image'                      => 'nullable|string',
        'elements'                   => 'required|array|min:1',
        'elements.*.name'            => 'required|string',
        'elements.*.products'        => 'required|array|min:1',
        'elements.*.products.*.id'   => 'required|exists:products,id',
    ];
}
}