<x-border-form :title="__('Suspek Penyakit Akibat Kecelakaan Kerja')" :align="__('start')" :bgcolor="__('bg-gray-50')">
    <div class="mt-4 space-y-3">

        {{-- Select Ya/Tidak (default Tidak) --}}
        <div>
            <x-select-input id="suspekAkibatKerja"
                wire:model.live="suspekAkibatKerja"
                :disabled="$isFormLocked"
                class="w-full mt-1">
                <option value="Tidak">Tidak</option>
                <option value="Ya">Ya</option>
            </x-select-input>
        </div>

        {{-- Keterangan --}}
        <div>
            <x-input-label value="Keterangan" />
            <x-text-input id="keteranganSuspekAkibatKerja"
                wire:model.live="dataDaftarPoliRJ.pemeriksaan.suspekAkibatKerja.keteranganSuspekAkibatKerja"
                placeholder="Keterangan" :error="$errors->has('dataDaftarPoliRJ.pemeriksaan.suspekAkibatKerja.keteranganSuspekAkibatKerja')" :disabled="$isFormLocked" class="w-full mt-1" />
            <x-input-error :messages="$errors->get('dataDaftarPoliRJ.pemeriksaan.suspekAkibatKerja.keteranganSuspekAkibatKerja')" class="mt-1" />
        </div>

    </div>
</x-border-form>
