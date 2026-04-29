@props([
    'disabled' => false,
    'error' => false,
])

@php
    $baseClass = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100
        focus:border-brand-lime focus:ring-brand-lime
        rounded-md shadow-sm w-full
        disabled:cursor-not-allowed
        disabled:bg-gray-100 disabled:text-gray-900
        dark:disabled:bg-gray-800 dark:disabled:text-gray-100';

    $errorClass = 'border-red-500 focus:border-red-500 focus:ring-red-500
        dark:border-red-400 dark:focus:border-red-400 dark:focus:ring-red-400';
@endphp

<input @disabled($disabled)
    {{ $attributes->merge([
        'class' => $error ? "$baseClass $errorClass" : $baseClass,
    ]) }}>
