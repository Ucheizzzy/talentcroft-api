<?php

namespace App\Http\Requests;

use App\Models\Movie;
use Illuminate\Foundation\Http\FormRequest;

class MovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'tags' => ['required', 'array'],
            'type' => ['required', 'string'],
            'age_rating' => ['required'],
            'director' => ['required', 'string'],
            'year' => ['required'],
            'genre' => ['required'],
            'title_image' => ['nullable']
        ];
    }
}
