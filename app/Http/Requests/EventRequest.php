<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validation for creating and editing an event (used by store and update)
class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        // Editing: only someone who can manage this event. Creating: the route's role middleware already checked.
        return $event ? $this->user()->can('manage', $event) : true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'target_courses' => ['nullable', 'array'],
            'target_courses.*' => ['in:'.implode(',', config('campus.courses'))],
            'target_year_levels' => ['nullable', 'array'],
            'target_year_levels.*' => ['integer', 'between:1,6'],
            'target_sections' => ['nullable', 'string', 'max:255'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
        ];
    }

    // Validated data, cleaned up and ready to save
    public function eventData(): array
    {
        $data = $this->validated();

        $data['target_courses'] = $data['target_courses'] ?? [];
        $data['target_year_levels'] = array_map('intval', $data['target_year_levels'] ?? []);
        $data['target_sections'] = collect(explode(',', $data['target_sections'] ?? ''))
            ->map(fn ($s) => strtoupper(trim($s)))
            ->filter()
            ->values()
            ->all();
        $data['requires_checkout'] = $this->boolean('requires_checkout');

        return $data;
    }
}
