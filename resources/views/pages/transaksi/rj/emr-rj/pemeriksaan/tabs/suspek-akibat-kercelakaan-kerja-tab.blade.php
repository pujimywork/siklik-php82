<x-border-form :title="__('Suspek Penyakit Akibat Kecelakaan Kerja')" :align="__('start')" :bgcolor="__('bg-gray-50')">
    <div class="mt-4 space-y-3">

        {{-- Radio Options --}}
        <div class="flex flex-wrap gap-3">
            @foreach ($dataDaftarPoliRJ['pemeriksaan']['suspekAkibatKerja']['suspekAkibatKerjaOptions'] ?? [] as $suspekAkibatKerjaOption)
                <x-radio-button :label="$suspekAkibatKerjaOption['suspekAkibatKerja']" :value="$suspekAkibatKerjaOption['suspekAkibatKerja']" name="suspekAkibatKerja"
                    wire:model.live="suspekAkibatKerja" :disabled="$isFormLocked" />
            @endforeach
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
