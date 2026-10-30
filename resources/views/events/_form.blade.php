@csrf
<div class="space-y-4">
    <div>
        <x-input-label for="title" value="Title" />
        <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $event->title ?? '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="venue" value="Venue" />
        <x-text-input id="venue" name="venue" class="block mt-1 w-full" :value="old('venue', $event->venue ?? '')" required />
        <x-input-error :messages="$errors->get('venue')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="starts_at" value="Starts at" />
        <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="block mt-1 w-full"
            :value="old('starts_at', isset($event) ? $event->starts_at->format('Y-m-d\TH:i') : '')" required />
        <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="ends_at" value="Ends at" />
        <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="block mt-1 w-full"
            :value="old('ends_at', isset($event) ? $event->ends_at->format('Y-m-d\TH:i') : '')" required />
        <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3"
            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $event->description ?? '') }}</textarea>
    </div>
    <div>
    <x-input-label value="Courses (none checked = everyone)" />
    <div class="mt-1 flex flex-wrap gap-4">
        @foreach (config('campus.courses') as $course)
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="target_courses[]" value="{{ $course }}"
                    @checked(in_array($course, old('target_courses', $event->target_courses ?? [])))>
                {{ $course }}
            </label>
        @endforeach
    </div>
</div>
<div>
    <x-input-label value="Year levels (none checked = everyone)" />
    <div class="mt-1 flex flex-wrap gap-4">
        @foreach ([1, 2, 3, 4] as $y)
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="target_year_levels[]" value="{{ $y }}"
                    @checked(in_array($y, old('target_year_levels', $event->target_year_levels ?? [])))>
                Year {{ $y }}
            </label>
        @endforeach
    </div>
</div>
<div>
    <x-input-label for="target_sections" value="Sections (comma-separated, blank = everyone)" />
    <x-text-input id="target_sections" name="target_sections" class="block mt-1 w-full"
        :value="old('target_sections', isset($event) ? implode(', ', $event->target_sections ?? []) : '')" />
</div>
<div>
    <x-input-label for="grace_minutes" value="Late after (minutes past start time)" />
    <x-text-input id="grace_minutes" name="grace_minutes" type="number" class="block mt-1 w-full"
        :value="old('grace_minutes', $event->grace_minutes ?? 15)" required />
</div>
<label class="inline-flex items-center gap-2">
    <input type="checkbox" name="requires_checkout" value="1"
        @checked(old('requires_checkout', $event->requires_checkout ?? false))>
    This event requires check-out too
</label>
    <x-primary-button>Save</x-primary-button>
</div>