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
    <x-primary-button>Save</x-primary-button>
</div>