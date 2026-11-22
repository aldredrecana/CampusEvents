<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validation for adding a student by student number (e.g. their phone died)
class ManualCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('scan', $this->route('event'));
    }

    // "2023-00101-cl-0 " becomes "2023-00101-CL-0" so it matches the saved number
    protected function prepareForValidation(): void
    {
        $this->merge(['student_number' => strtoupper(trim((string) $this->student_number))]);
    }

    public function rules(): array
    {
        return [
            'student_number' => ['required', 'string', 'max:30'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }
}
