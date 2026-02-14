<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'target_audience' => ['required', 'string', 'in:all,students,teachers,parents'],
            'image' => ['nullable', 'mimes:jpeg,png,jpg,gif,mp4,mov,avi', 'max:40480'],
            'is_pinned' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The announcement title is required.',
            'content.required' => 'The announcement content is required.',
            'target_audience.required' => 'Please select a target audience.',
            'target_audience.in' => 'Invalid target audience selected.',
            'image.mimes' => 'Only JPEG, PNG, GIF, MP4, MOV, and AVI files are allowed.',
            'image.max' => 'The media file must not exceed 40MB.',
            'expires_at.after_or_equal' => 'The expiration date must be after or equal to the publish date.',
        ];
    }
}
