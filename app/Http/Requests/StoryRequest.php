<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|url',
            'published_at' => 'required|date',
            'category_id' => 'required|exists:categories,id'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título de la noticia es obligatorio.',
            'title.string' => 'El título de la noticia debe ser una cadena de texto.',
            'title.max' => 'El título de la noticia no puede exceder los 200 caracteres.',
            'description.required' => 'La descripción de la noticia es obligatoria.',
            'description.string' => 'La descripción de la noticia debe ser una cadena de texto.',
            'description.max' => 'La descripción de la noticia no puede exceder los 255 caracteres.',
            'content.required' => 'El contenido de la noticia es obligatorio.',
            'content.string' => 'El contenido de la noticia debe ser una cadena de texto.',
            'image_url.url' => 'La URL de la imagen debe ser una URL válida.',
            'published_at.required' => 'La fecha de publicación es obligatoria.',
            'published_at.date' => 'La fecha de publicación debe ser una fecha válida.',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no existe.'
        ];
    }
}
