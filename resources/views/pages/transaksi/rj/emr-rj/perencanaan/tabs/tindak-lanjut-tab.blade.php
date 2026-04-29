<x-border-form :title="__('Tindak Lanjut')" :align="__('start')" :bgcolor="__('bg-gray-50')">
    <div class="mt-4 space-y-4">

        {{-- Select Tindak Lanjut (BPJS PCare StatusPulang dari ref_bpjs_table) --}}
        <div>
            <x-input-label value="Status Pulang (BPJS)" :required="true" />
            @php $opts = $this->statusPulangOptions; @endphp
            <x-select-input id="kdStatusPulang"
                wire:change="changeStatusPulang($event.target.value)"
                :value="$dataDaftarPoliRJ['perencanaan']['kdStatusPulang'] ?? ''"
                :disabled="$isFormLocked"
                class="w-full mt-1">
                <option value="">— Pilih Status Pulang —</option>
                @foreach ($opts as $opt)
                    <option value="{{ $opt['kd'] }}">
                        {{ $opt['kd'] }} — {{ $opt['nm'] }}
                    </option>
                @endforeach
            </x-select-input>
            @if (empty($opts))
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                    Cache StatusPulang BPJS kosong. Sync dulu di
                    <a href="{{ route('master.ref-bpjs') }}" wire:navigate class="underline">Master Ref BPJS</a>.
                </p>
            @endif
        </div>

        {{-- Keterangan --}}
        <div>
            <x-input-label value="Keterangan Tindak Lanjut" />
            <x-text-input id="keteranganTindakLanjut" placeholder="Keterangan Tindak Lanjut"
                :error="$errors->has('dataDaftarPoliRJ.perencanaan.tindakLanjut.keteranganTindakLanjut')"
                :disabled="$isFormLocked"
                wire:model.live="dataDaftarPoliRJ.perencanaan.tindakLanjut.keteranganTindakLanjut"
                class="w-full mt-1" />
            <x-input-error :messages="$errors->get('dataDaftarPoliRJ.perencanaan.tindakLanjut.keteranganTindakLanjut')" class="mt-1" />
        </div>

    </div>
</x-border-form>
