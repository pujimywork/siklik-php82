<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\BPJS\PcareTrait;

new class extends Component {
    use PcareTrait;

    /* -------------------------
     | Konfigurasi kategori cache (label → method PcareTrait + args)
     |
     | Pola contek dari siklik-lite. Tabel ref_bpjs_table simpan list
     | response BPJS PCare yg jarang berubah, dipakai oleh LOV
     | (alergi, prognosa, dll) supaya nggak hit API tiap kali.
     * ------------------------- */
    public array $categories = [
        ['key' => 'Kesadaran',      'desc' => 'Daftar kode kesadaran (Compos Mentis, Apatis, dll).'],
        ['key' => 'Spesialis',      'desc' => 'Daftar spesialisasi dokter BPJS.'],
        ['key' => 'Prognosa',       'desc' => 'Kode prognosa pasien (Sanam, Bonam, dll).'],
        ['key' => 'PoliFktp',       'desc' => 'Daftar poli BPJS untuk klinik pratama (FKTP).'],
        ['key' => 'StatusPulang',   'desc' => 'Kode status pulang pasien (Berobat Jalan, Rujuk, dll).'],
        ['key' => 'Sarana',         'desc' => 'Sarana penunjang BPJS.'],
        ['key' => 'Alergi Makanan', 'desc' => 'Daftar referensi alergi makanan BPJS.'],
        ['key' => 'Alergi Udara',   'desc' => 'Daftar referensi alergi udara BPJS.'],
        ['key' => 'Alergi Obat',    'desc' => 'Daftar referensi alergi obat BPJS.'],
        ['key' => 'Dokter',         'desc' => 'Master dokter terdaftar di BPJS PCare.'],
        ['key' => 'Provider',       'desc' => 'Daftar faskes provider rayonisasi.'],
    ];

    /* -------------------------
     | Update single kategori → call BPJS API → upsert ke ref_bpjs_table
     * ------------------------- */
    public function updateRef(string $key): void
    {
        try {
            $list = $this->fetchListByKey($key);
            if ($list === null) {
                $this->dispatch('toast', type: 'error',
                    message: "Kategori tidak dikenal: {$key}", title: 'Master Ref BPJS');
                return;
            }
            if (!is_array($list) || empty($list)) {
                $this->dispatch('toast', type: 'warning',
                    message: "BPJS tidak mengembalikan data untuk {$key}.",
                    title: 'Master Ref BPJS');
                return;
            }

            $jsonNew = json_encode($list, true);
            $jsonOld = (string) (DB::table('ref_bpjs_table')
                ->where('ref_keterangan', $key)->value('ref_json') ?? '');

            if ($jsonNew === $jsonOld) {
                $this->dispatch('toast', type: 'info',
                    message: "{$key}: data BPJS sudah sama dgn cache lokal.",
                    title: 'Master Ref BPJS');
                return;
            }

            $exists = DB::table('ref_bpjs_table')->where('ref_keterangan', $key)->exists();
            if ($exists) {
                DB::table('ref_bpjs_table')
                    ->where('ref_keterangan', $key)
                    ->update([
                        'ref_json'   => $jsonNew,
                        'updated_at' => DB::raw('SYSTIMESTAMP'),
                    ]);
            } else {
                DB::table('ref_bpjs_table')->insert([
                    'ref_keterangan' => $key,
                    'ref_json'       => $jsonNew,
                    'updated_at'     => DB::raw('SYSTIMESTAMP'),
                ]);
            }

            $this->dispatch('toast', type: 'success',
                message: "{$key}: cache lokal di-update (" . count($list) . " entries).",
                title: 'Master Ref BPJS', duration: 5000);

            unset($this->refRows);
        } catch (\Exception $e) {
            \Log::error('Master Ref BPJS update exception', ['key' => $key, 'error' => $e->getMessage()]);
            $this->dispatch('toast', type: 'error',
                message: 'Error: ' . $e->getMessage(), title: 'BPJS Error');
        }
    }

    /* Mapping label → call PcareTrait. Return list (array) atau null kalau key tdk dikenal. */
    private function fetchListByKey(string $key): ?array
    {
        $resp = match ($key) {
            'Kesadaran'      => $this->getKesadaran()->getOriginalContent(),
            'Spesialis'      => $this->getSpesialis()->getOriginalContent(),
            'Prognosa'       => $this->getPrognosa()->getOriginalContent(),
            'PoliFktp'       => $this->getPoliFktp(0, 100)->getOriginalContent(),
            'StatusPulang'   => $this->getStatusPulang('0')->getOriginalContent(),
            'Sarana'         => $this->getSarana()->getOriginalContent(),
            'Alergi Makanan' => $this->getAlergi('01')->getOriginalContent(),
            'Alergi Udara'   => $this->getAlergi('02')->getOriginalContent(),
            'Alergi Obat'    => $this->getAlergi('03')->getOriginalContent(),
            'Dokter'         => $this->getDokter(0, 200)->getOriginalContent(),
            'Provider'       => $this->getProviderRayonisasi(0, 100)->getOriginalContent(),
            default          => null,
        };
        if ($resp === null) return null;

        $code = $resp['metadata']['code'] ?? 0;
        if ($code != 200) {
            $msg = $resp['metadata']['message'] ?? "code {$code}";
            throw new \RuntimeException("BPJS {$key}: {$msg}");
        }

        return $resp['response']['list'] ?? $resp['response'] ?? [];
    }

    /* -------------------------
     | Cache row metadata (count + updated_at) per kategori
     * ------------------------- */
    #[Computed]
    public function refRows(): array
    {
        $rows = DB::table('ref_bpjs_table')
            ->select('ref_keterangan', 'ref_json',
                DB::raw("to_char(updated_at, 'dd/mm/yyyy hh24:mi:ss') as updated_at_str"))
            ->get()
            ->keyBy('ref_keterangan');

        $out = [];
        foreach ($this->categories as $cat) {
            $row = $rows->get($cat['key']);
            $count = 0;
            if ($row && $row->ref_json) {
                $decoded = json_decode($row->ref_json, true);
                $count = is_array($decoded) ? count($decoded) : 0;
            }
            $out[$cat['key']] = [
                'count'      => $count,
                'updated_at' => $row->updated_at_str ?? null,
            ];
        }
        return $out;
    }
};
?>

<div>
    <header class="bg-white shadow dark:bg-gray-800">
        <div class="w-full px-4 py-2 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-gray-100">
                Master Reference BPJS PCare
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Cache lokal untuk reference BPJS yg jarang berubah (alergi, kesadaran, prognosa, dll).
                Klik <span class="font-medium">Update</span> untuk sync per kategori.
            </p>
        </div>
    </header>

    <div class="w-full min-h-[calc(100vh-5rem-72px)] bg-white dark:bg-gray-800">
        <div class="px-6 pt-4 pb-6">
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl dark:border-gray-700 dark:bg-gray-900">
                <table class="min-w-full text-sm">
                    <thead class="text-gray-600 bg-gray-50 dark:bg-gray-800 dark:text-gray-200">
                        <tr class="text-left">
                            <th class="px-4 py-3 font-semibold w-1/4">Kategori</th>
                            <th class="px-4 py-3 font-semibold">Deskripsi</th>
                            <th class="px-4 py-3 font-semibold w-24 text-right">Jumlah</th>
                            <th class="px-4 py-3 font-semibold w-48">Last Sync</th>
                            <th class="px-4 py-3 font-semibold w-32 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 divide-y divide-gray-200 dark:divide-gray-700 dark:text-gray-200">
                        @foreach ($categories as $cat)
                            @php $meta = $this->refRows[$cat['key']] ?? ['count' => 0, 'updated_at' => null]; @endphp
                            <tr wire:key="ref-bpjs-{{ $cat['key'] }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                <td class="px-4 py-3 font-semibold">{{ $cat['key'] }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $cat['desc'] }}</td>
                                <td class="px-4 py-3 text-right font-mono">
                                    @if ($meta['count'] > 0)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            {{ $meta['count'] }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $meta['updated_at'] ?? '— belum pernah sync —' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-secondary-button type="button"
                                        wire:click="updateRef('{{ $cat['key'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="updateRef('{{ $cat['key'] }}')"
                                        class="px-3 py-1 text-xs">
                                        <span wire:loading.remove wire:target="updateRef('{{ $cat['key'] }}')">Update</span>
                                        <span wire:loading wire:target="updateRef('{{ $cat['key'] }}')">...</span>
                                    </x-secondary-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
