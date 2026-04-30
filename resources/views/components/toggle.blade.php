{{-- resources/views/components/toggle.blade.php --}}
{{--
    Pemakaian:
      Mode 1 (model binding):
        <x-toggle wire:model.live="passStatus" trueValue="N" falseValue="O" label="Pasien Baru" :disabled="$isFormLocked" />
        <x-toggle wire:model.live="dataDaftarUGD.passStatus" trueValue="N" falseValue="O" label="Pasien Baru" />
        <x-toggle wire:model.live="activeStatus" trueValue="1" falseValue="0">Status Aktif</x-toggle>

      Mode 2 (per-row di table — pakai `current` + `wireClick`):
        <x-toggle :current="$row->active_record" trueValue="1" falseValue="0"
                  wireClick="toggleActive('{{ $row->emp_id }}')">Aktif</x-toggle>

    Catatan:
      - Visual driven by server-rendered class — Alpine cuma jadi click handler.
        Cegah bug "klik 2x kembali tercentang" akibat Alpine optimistic flip vs
        Livewire morph re-render yang nggak sync.
      - Mendukung nested array (dataDaftarXxx.field) tanpa @entangle.
      - Nilai awal diambil via $current (jika diisi) atau data_get($__livewire, $wireModel).
      - Toggle via $wire.set() (Mode 1) atau $wire.{method}(...) (Mode 2).
--}}

@props([
    'trueValue' => 'Y',
    'falseValue' => 'N',
    'label' => null,
    'disabled' => false,
    'current' => null,
    'wireClick' => null,
    'size' => 'sm',
])

@php
    $wireModel = $attributes->whereStartsWith('wire:model')->first();

    $currentValue = null;
    if ($current !== null) {
        $currentValue = $current;
    } elseif ($wireModel && isset($__livewire)) {
        try {
            $currentValue = data_get($__livewire, $wireModel);
        } catch (\Throwable) {
        }
    }
    $currentValue ??= $falseValue;

    // Server-side: tentukan ON/OFF di Blade, bukan di Alpine.
    $isOn = $currentValue == $trueValue;
    $nextValue = $isOn ? $falseValue : $trueValue;

    $attrs = $attributes->whereDoesntStartWith('wire:model');

    // Class untuk track + thumb berdasarkan state server.
    $trackClass = match (true) {
        $isOn && $disabled => 'bg-gray-400',
        $isOn => 'bg-brand',
        !$isOn && $disabled => 'bg-gray-200',
        default => 'bg-gray-300',
    };

    // Size variants — default 'sm' menjaga kompatibilitas dgn pemakaian existing.
    [$trackSize, $thumbSize, $thumbOn, $thumbOff, $labelSize] = match ($size) {
        'lg'    => ['h-8 w-14',  'w-6 h-6 mt-1',   'translate-x-7 ml-1', 'translate-x-1', 'text-base'],
        'md'    => ['h-7 w-[3.25rem]', 'w-5 h-5 mt-1', 'translate-x-7 ml-1', 'translate-x-1', 'text-sm'],
        default => ['h-6 w-11',  'w-4 h-4 mt-1',   'translate-x-6 ml-1', 'translate-x-1', 'text-sm'],
    };
    $thumbClass = $isOn ? $thumbOn : $thumbOff;
@endphp

<div x-data="{
    disabled: @js($disabled),
    toggle() {
        if (this.disabled) return;
        @if ($wireModel) $wire.set('{{ $wireModel }}', @js($nextValue)); @endif
        @if ($wireClick) $wire.{{ $wireClick }}; @endif
    }
}"
    @click="toggle"
    {{ $attrs->merge([
        'class' => 'flex items-center space-x-2 ' . ($disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'),
    ]) }}>
    <div class="transition rounded-full {{ $trackSize }} {{ $trackClass }}">
        <div class="transition transform bg-white rounded-full shadow {{ $thumbSize }} {{ $thumbClass }}"></div>
    </div>

    @if ($label)
        <span class="block font-medium text-gray-700 dark:text-gray-300 {{ $labelSize }} {{ $disabled ? 'opacity-60' : '' }}">{{ $label }}</span>
    @elseif (trim((string) $slot) !== '')
        <span class="block font-medium text-gray-700 dark:text-gray-300 {{ $labelSize }} {{ $disabled ? 'opacity-60' : '' }}">{{ $slot }}</span>
    @endif
</div>
