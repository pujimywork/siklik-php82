@props([
    'type' => 'button',
    'disabled' => false,
])

<button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->class([
        // layout — sama persis dengan semua button lainnya
        'inline-flex items-center justify-center gap-2',
        'px-5 py-2.5 rounded-lg',
        'text-sm font-medium',
        'transition-colors duration-150',
    
        // light mode — red solid
        'text-white bg-red-600',
        'hover:bg-red-700',
        'focus:outline-none focus:ring-4 focus:ring-red-300',
    
        // dark mode
        'dark:bg-red-600',
        'dark:hover:bg-red-700',
        'dark:focus:ring-red-900',
    
        // disabled
        'disabled:opacity-50',
        'disabled:cursor-not-allowed',
        'disabled:pointer-events-none',
    ]) }}>
    {{ $slot }}
</button>
