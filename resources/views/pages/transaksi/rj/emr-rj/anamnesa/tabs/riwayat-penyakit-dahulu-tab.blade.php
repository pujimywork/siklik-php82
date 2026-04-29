<x-border-form :title="__('Riwayat & Alergi')" :align="__('start')" :bgcolor="__('bg-gray-50')">
    <div class="mt-4 space-y-4">

        {{-- Riwayat Penyakit Dahulu --}}
        <div>
            <x-input-label for="dataDaftarPoliRJ.anamnesa.riwayatPenyakitDahulu.riwayatPenyakitDahulu"
                value="Riwayat Penyakit Dahulu" :required="true" />

            <x-textarea id="dataDaftarPoliRJ.anamnesa.riwayatPenyakitDahulu.riwayatPenyakitDahulu"
                wire:model.live="dataDaftarPoliRJ.anamnesa.riwayatPenyakitDahulu.riwayatPenyakitDahulu"
                placeholder="Riwayat Perjalanan Penyakit" :error="$errors->has('dataDaftarPoliRJ.anamnesa.riwayatPenyakitDahulu.riwayatPenyakitDahulu')" :disabled="$isFormLocked" :rows="3"
                class="w-full mt-1" />

            <x-input-error :messages="$errors->get('dataDaftarPoliRJ.anamnesa.riwayatPenyakitDahulu.riwayatPenyakitDahulu')" class="mt-1" />
        </div>

        {{-- Alergi --}}
        <div>
            <x-input-label for="dataDaftarPoliRJ.anamnesa.alergi.alergi" value="Alergi" :required="false" />

            <x-textarea id="dataDaftarPoliRJ.anamnesa.alergi.alergi"
                wire:model.live="dataDaftarPoliRJ.anamnesa.alergi.alergi"
                placeholder="Jenis Alergi — Makanan / Obat / Udara" :error="$errors->has('dataDaftarPoliRJ.anamnesa.alergi.alergi')" :disabled="$isFormLocked"
                :rows="3" class="w-full mt-1" />

            <x-input-error :messages="$errors->get('dataDaftarPoliRJ.anamnesa.alergi.alergi')" class="mt-1" />
        </div>

        {{-- SNOMED CT — Alergi (untuk Satu Sehat) --}}
        <div>
            <livewire:lov.snomed.lov-snomed
                target="alergiSnomed"
                label="Kode SNOMED Alergi (Satu Sehat)"
                placeholder="Ketik nama alergi / obat..."
                valueSet="substance-code"
                :initialSnomedCode="$dataDaftarPoliRJ['anamnesa']['alergi']['snomedCode'] ?? null"
                :disabled="$isFormLocked"
                wire:key="lov-snomed-alergi-{{ $rjNo ?? 'new' }}-{{ $renderVersions['modal-anamnesa-rj'] ?? 0 }}"
            />
        </div>

        {{-- ============================================================
             Alergi BPJS PCare — Makanan / Udara / Obat (klinik pratama)
             ============================================================ --}}
        @php
            $alergi = $dataDaftarPoliRJ['anamnesa']['alergi'] ?? [];
            $alergiBlocks = [
                ['title' => 'Alergi Makanan', 'jenis' => '01', 'method' => 'loadAlergiMakanan',
                 'options' => $alergiMakananOptions, 'kd' => 'alergiMakan', 'desc' => 'alergiMakanDesc'],
                ['title' => 'Alergi Udara',   'jenis' => '02', 'method' => 'loadAlergiUdara',
                 'options' => $alergiUdaraOptions,   'kd' => 'alergiUdara', 'desc' => 'alergiUdaraDesc'],
                ['title' => 'Alergi Obat',    'jenis' => '03', 'method' => 'loadAlergiObat',
                 'options' => $alergiObatOptions,    'kd' => 'alergiObat',  'desc' => 'alergiObatDesc'],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-4 pt-2 sm:grid-cols-3">
            @foreach ($alergiBlocks as $blk)
                <div>
                    <x-input-label :value="$blk['title'] . ' (BPJS)'" />
                    <div class="flex gap-1 mt-1">
                        <x-text-input
                            :value="($alergi[$blk['kd']] ?? '00') . ' / ' . ($alergi[$blk['desc']] ?? 'Tidak Ada')"
                            disabled class="flex-1 bg-gray-50 dark:bg-gray-800" />
                        <x-secondary-button type="button"
                            wire:click="{{ $blk['method'] }}" wire:loading.attr="disabled"
                            wire:target="{{ $blk['method'] }}" :disabled="$isFormLocked"
                            class="px-2 whitespace-nowrap">
                            <span wire:loading.remove wire:target="{{ $blk['method'] }}">Cari BPJS</span>
                            <span wire:loading wire:target="{{ $blk['method'] }}">...</span>
                        </x-secondary-button>
                    </div>

                    @if (!empty($blk['options']))
                        <div class="z-10 mt-1 overflow-hidden bg-white border border-gray-200 shadow rounded-xl dark:bg-gray-900 dark:border-gray-700">
                            <ul class="overflow-y-auto divide-y divide-gray-100 max-h-48 dark:divide-gray-800">
                                @foreach ($blk['options'] as $opt)
                                    <li>
                                        <button type="button"
                                            wire:click="selectAlergi('{{ $blk['jenis'] }}', '{{ $opt['kdAlergi'] }}', @js($opt['nmAlergi']))"
                                            class="w-full px-3 py-2 text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-800">
                                            <span class="font-mono text-xs text-gray-500">{{ $opt['kdAlergi'] }}</span>
                                            <span class="ml-2">{{ $opt['nmAlergi'] }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
</x-border-form>
