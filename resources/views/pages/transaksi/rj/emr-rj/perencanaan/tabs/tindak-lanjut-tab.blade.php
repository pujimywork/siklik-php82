<x-border-form :title="__('Tindak Lanjut')" :align="__('start')" :bgcolor="__('bg-gray-50')">
    <div class="mt-4 space-y-4">

        {{-- Select Tindak Lanjut --}}
        <div>
            <x-select-input id="tindakLanjut" wire:model.live="dataDaftarPoliRJ.perencanaan.tindakLanjut.tindakLanjut"
                :disabled="$isFormLocked" :error="$errors->has('dataDaftarPoliRJ.perencanaan.tindakLanjut.tindakLanjut')">
                <option value="">Pilih Tindak Lanjut</option>
                @foreach ($dataDaftarPoliRJ['perencanaan']['tindakLanjut']['tindakLanjutOptions'] ?? [] as $option)
                    <option value="{{ $option['tindakLanjut'] }}">
                        {{ __($option['tindakLanjut']) }}
                    </option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('dataDaftarPoliRJ.perencanaan.tindakLanjut.tindakLanjut')" class="mt-1" />
        </div>

        {{-- Keterangan --}}
        <div>
            <x-text-input id="keteranganTindakLanjut" placeholder="Keterangan Tindak Lanjut" :error="$errors->has('dataDaftarPoliRJ.perencanaan.tindakLanjut.keteranganTindakLanjut')"
                :disabled="$isFormLocked" wire:model.live="dataDaftarPoliRJ.perencanaan.tindakLanjut.keteranganTindakLanjut" />
            <x-input-error :messages="$errors->get('dataDaftarPoliRJ.perencanaan.tindakLanjut.keteranganTindakLanjut')" class="mt-1" />
        </div>

    </div>
</x-border-form>
