<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_number' => 'required|unique:rooms,room_number',
            'description' => 'required|string',
            'is_available' => 'required|boolean|in:0,1|default:1',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ];
    }}
