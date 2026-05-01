@props([
    'title' => '',
    'align' => 'start',
    'bgcolor' => 'bg-white',
    'class' => '',
    'titleClass' => '',
    'padding' => 'p-2',
])

@php
    // Set alignment class
    $alignClass = match ($align) {
        'center' => 'text-center',
        'end' => 'text-right',
        'right' => 'text-right',
        default => 'text-left',
    };

    // Set padding for content based on title existence
    $contentPadding = $title ? 'pt-1' : 'pt-2';
@endphp

<div
    {{ $attributes->merge(['class' => "border border-gray-200 rounded-2xl shadow-sm dark:border-gray-700 {$bgcolor} dark:bg-gray-900 {$class}"]) }}>
    @if ($title)
        <div class="px-2 py-1 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 {{ $alignClass }} {{ $titleClass }}">
                {{ $title }}
            </h3>
        </div>
    @endif

    <div class="{{ $padding }} {{ !$title ? $contentPadding : '' }}">
        {{ $slot }}
    </div>
</div>
