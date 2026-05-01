<div class="space-y-4">

    @php
        $tv  = $dataDaftarPoliRJ['pemeriksaan']['tandaVital'] ?? [];
        $nut = $dataDaftarPoliRJ['pemeriksaan']['nutrisi']    ?? [];
    @endphp

    {{-- TANDA VITAL --}}
    <x-border-form :title="__('Tanda Vital')" :align="__('start')" :bgcolor="__('bg-gray-50')">
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6">

            {{-- Row 1 --}}
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Keadaan Umum</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['keadaanUmum'] ?? '-' }}
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tingkat Kesadaran</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['tingkatKesadaran'] ?? '-' }}
                </span>
            </div>

            {{-- Row 2 --}}
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tekanan Darah</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['sistolik'] ?? '-' }} / {{ $tv['distolik'] ?? '-' }}
                    <span class="text-xs text-gray-400">mmHg</span>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Frekuensi Nadi</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['frekuensiNadi'] ?? '-' }}
                    <span class="text-xs text-gray-400">x/menit</span>
                </span>
            </div>

            {{-- Row 3 --}}
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Frekuensi Nafas</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['frekuensiNafas'] ?? '-' }}
                    <span class="text-xs text-gray-400">x/menit</span>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Suhu</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['suhu'] ?? '-' }}
                    <span class="text-xs text-gray-400">°C</span>
                </span>
            </div>

            {{-- Row 4 --}}
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 sm:border-b-0">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">SPO2</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['spo2'] ?? '-' }}
                    <span class="text-xs text-gray-400">%</span>
                </span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">GDA</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $tv['gda'] ?? '-' }}
                    <span class="text-xs text-gray-400">g/dl</span>
                </span>
            </div>

        </div>
    </x-border-form>

    {{-- NUTRISI --}}
    <x-border-form :title="__('Nutrisi')" :align="__('start')" :bgcolor="__('bg-gray-50')">
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6">

            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Berat Badan</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $nut['bb'] ?? '-' }}
                    <span class="text-xs text-gray-400">Kg</span>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tinggi Badan</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $nut['tb'] ?? '-' }}
                    <span class="text-xs text-gray-400">Cm</span>
                </span>
            </div>

            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Index Masa Tubuh</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $nut['imt'] ?? '-' }}
                    <span class="text-xs text-gray-400">Kg/M²</span>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Lingkar Kepala</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $nut['lk'] ?? '-' }}
                    <span class="text-xs text-gray-400">Cm</span>
                </span>
            </div>

            <div class="flex items-center justify-between py-2 sm:border-b-0">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Lingkar Lengan Atas</span>
                <span class="text-sm font-medium text-right text-gray-800 dark:text-gray-200">
                    {{ $nut['lila'] ?? '-' }}
                    <span class="text-xs text-gray-400">Cm</span>
                </span>
            </div>

        </div>
    </x-border-form>

</div>
