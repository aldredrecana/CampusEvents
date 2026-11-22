@props(['disabled' => false])

<input @disabled($disabled)
    {{ $attributes->merge(['class' => 'border-gray-300 focus:border-maroon-600 focus:ring-maroon-600 rounded-md shadow-sm']) }}>
