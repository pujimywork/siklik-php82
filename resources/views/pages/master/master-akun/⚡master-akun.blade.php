<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    public string $searchKeyword = '';
    public int    $itemsPerPage  = 10;
    public string $filterGroup   = '';   // gra_id filter
    public string $filterKas     = '';   // '1' = kas only

    public function updatedSearchKeyword(): void { $this->resetPage(); }
    public function updatedItemsPerPage(): void  { $this->resetPage(); }
    public function updatedFilterGroup(): void   { $this->resetPage(); }
    public function updatedFilterKas(): void     { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->dispatch('master.akun.openCreate');
    }

    public function openEdit(string $accId): void
    {
        $this->dispatch('master.akun.openEdit', accId: $accId);
    }

    public function requestDelete(string $accId): void
    {
        $this->dispatch('master.akun.requestDelete', accId: $accId);
    }

    public function toggleActive(string $accId): void
    {
        $cur = (string) DB::table('tkacc_accountses')->where('acc_id', $accId)->value('active_status');
        $next = $cur === '1' ? '0' : '1';
        DB::table('tkacc_accountses')->where('acc_id', $accId)->update(['active_status' => $next]);
        $this->dispatch('toast', type: 'success',
            message: 'Status akun → ' . ($next === '1' ? 'Aktif' : 'Non-aktif'));
        $this->resetPage();
    }

    public function toggleKas(string $accId): void
    {
        $cur = (string) DB::table('tkacc_accountses')->where('acc_id', $accId)->value('kas_status');
        $next = $cur === '1' ? '0' : '1';
        DB::table('tkacc_accountses')->where('acc_id', $accId)->update(['kas_status' => $next]);
        $this->dispatch('toast', type: 'success',
            message: 'Tipe akun → ' . ($next === '1' ? 'Akun Kas' : 'Bukan Kas'));
        $this->resetPage();
    }

    #[On('master.akun.saved')]
    public function refreshAfterSaved(): void { $this->resetPage(); }

    #[Computed]
    public function groupOptions()
    {
        return DB::table('tkacc_gr_accountses')
            ->select('gra_id', 'gra_desc')
            ->orderBy('gra_id')
            ->get();
    }

    #[Computed]
    public function rows()
    {
        $q = DB::table('tkacc_accountses as a')
            ->leftJoin('tkacc_gr_accountses as g', 'g.gra_id', '=', 'a.gra_id')
            ->select('a.acc_id', 'a.acc_desc', 'a.active_status', 'a.kas_status',
                'a.gra_id', 'g.gra_desc', 'a.acc_dk_status')
            ->orderByRaw("CASE WHEN a.active_status = '1' THEN 0 ELSE 1 END")
            ->orderBy('a.acc_id');

        if (trim($this->searchKeyword) !== '') {
            $kw = mb_strtoupper(trim($this->searchKeyword));
            $q->where(function ($sub) use ($kw) {
                $sub->whereRaw('UPPER(a.acc_id) LIKE ?', ["%{$kw}%"])
                    ->orWhereRaw('UPPER(a.acc_desc) LIKE ?', ["%{$kw}%"])
                    ->orWhereRaw('UPPER(g.gra_desc) LIKE ?', ["%{$kw}%"]);
            });
        }
        if ($this->filterGroup !== '') {
            $q->where('a.gra_id', $this->filterGroup);
        }
        if ($this->filterKas === '1') {
            $q->where('a.kas_status', '1');
        } elseif ($this->filterKas === '0') {
            $q->where('a.kas_status', '0');
        }

        return $q->paginate($this->itemsPerPage);
    }

    public function resetFilters(): void
    {
        $this->reset(['searchKeyword', 'filterGroup', 'filterKas']);
        $this->resetPage();
    }
};
?>

<div>
    <header class="bg-white shadow dark:bg-gray-800">
        <div class="w-full px-4 py-2 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-gray-100">
                Master Akun (Chart of Accounts)
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Daftar akun pusat. Sumber: <span class="font-mono">tkacc_accountses</span>.
                Akun bertipe <em>Kas</em> dipakai oleh kasir + cara bayar.
            </p>
        </div>
    </header>

    <div class="w-full min-h-[calc(100vh-5rem-72px)] bg-white dark:bg-gray-800">
        <div class="px-6 pt-2 pb-6">

            <div class="sticky z-30 px-4 py-3 bg-white border-b border-gray-200 top-20 dark:bg-gray-900 dark:border-gray-700">
                @php
                    $filterActive = trim($searchKeyword) !== '' || $filterGroup !== '' || $filterKas !== '';
                @endphp

                <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">

                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end">
                        <div class="w-full sm:w-72">
                            <x-input-label for="searchKeyword" value="Cari" class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400" />
                            <x-text-input id="searchKeyword" type="text"
                                wire:model.live.debounce.300ms="searchKeyword"
                                placeholder="Kode / nama akun / group..."
                                class="block w-full" />
                        </div>

                        <div class="w-full sm:w-60">
                            <x-input-label for="filterGroup" value="Group Akun" class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400" />
                            <x-select-input id="filterGroup" wire:model.live="filterGroup" class="block w-full">
                                <option value="">— Semua Group —</option>
                                @foreach ($this->groupOptions as $g)
                                    <option value="{{ $g->gra_id }}">{{ $g->gra_id }} — {{ $g->gra_desc }}</option>
                                @endforeach
                            </x-select-input>
                        </div>

                        <div class="w-full sm:w-44">
                            <x-input-label for="filterKas" value="Tipe Akun" class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400" />
                            <x-select-input id="filterKas" wire:model.live="filterKas" class="block w-full">
                                <option value="">— Semua Tipe —</option>
                                <option value="1">Akun Kas</option>
                                <option value="0">Bukan Kas</option>
                            </x-select-input>
                        </div>

                        @if ($filterActive)
                            <div>
                                <x-secondary-button type="button" wire:click="resetFilters" class="px-3 py-2 text-xs">
                                    Reset Filter
                                </x-secondary-button>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-end justify-end gap-2">
                        <div class="w-28">
                            <x-input-label for="itemsPerPage" value="Per Halaman" class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400" />
                            <x-select-input id="itemsPerPage" wire:model.live="itemsPerPage" class="block w-full">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="100">100</option>
                            </x-select-input>
                        </div>
                        <x-primary-button type="button" wire:click="openCreate">
                            + Tambah Akun
                        </x-primary-button>
                    </div>
                </div>
            </div>

            <div class="mt-4 bg-white border border-gray-200 shadow-sm rounded-2xl dark:border-gray-700 dark:bg-gray-900">
                <div class="overflow-x-auto overflow-y-auto max-h-[calc(100dvh-320px)] rounded-t-2xl">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 z-10 text-gray-600 bg-gray-50 dark:bg-gray-800 dark:text-gray-200">
                            <tr class="text-left">
                                <th class="px-4 py-3 font-semibold">ID</th>
                                <th class="px-4 py-3 font-semibold">DESKRIPSI</th>
                                <th class="px-4 py-3 font-semibold">GROUP</th>
                                <th class="px-4 py-3 font-semibold w-20 text-center">D/K</th>
                                <th class="px-4 py-3 font-semibold w-20 text-center">KAS</th>
                                <th class="px-4 py-3 font-semibold w-24 text-center">STATUS</th>
                                <th class="px-4 py-3 font-semibold w-56">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 divide-y divide-gray-200 dark:divide-gray-700 dark:text-gray-200">
                            @forelse ($this->rows as $row)
                                <tr wire:key="akun-{{ $row->acc_id }}"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $row->acc_id }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $row->acc_desc }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $row->gra_id }}
                                        @if (!empty($row->gra_desc))
                                            — {{ $row->gra_desc }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ((string) $row->acc_dk_status === 'D')
                                            <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700">D</span>
                                        @elseif ((string) $row->acc_dk_status === 'K')
                                            <span class="px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-700">K</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ((string) $row->kas_status === '1')
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800">Kas</span>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ((string) $row->active_status === '1')
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Non-aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <x-secondary-button type="button"
                                                wire:click="openEdit('{{ $row->acc_id }}')" class="px-2 py-1 text-xs">
                                                Edit
                                            </x-secondary-button>
                                            <x-secondary-button type="button"
                                                wire:click="toggleKas('{{ $row->acc_id }}')" class="px-2 py-1 text-xs"
                                                title="Set/unset sebagai akun Kas">
                                                {{ (string) $row->kas_status === '1' ? 'Bukan Kas' : 'Set Kas' }}
                                            </x-secondary-button>
                                            <x-confirm-button variant="danger"
                                                :action="'requestDelete(\'' . $row->acc_id . '\')'"
                                                title="Hapus Akun"
                                                message="Yakin hapus akun {{ $row->acc_desc }}?"
                                                confirmText="Ya, hapus" cancelText="Batal"
                                                class="px-2 py-1 text-xs">
                                                Hapus
                                            </x-confirm-button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                        Data akun tidak ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="sticky bottom-0 z-10 px-4 py-3 bg-white border-t border-gray-200 rounded-b-2xl dark:bg-gray-900 dark:border-gray-700">
                    {{ $this->rows->links() }}
                </div>
            </div>

            <livewire:pages::master.master-akun.master-akun-actions wire:key="master-akun-actions" />
        </div>
    </div>
</div>
