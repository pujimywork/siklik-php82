@php
    use Illuminate\Support\Str;

    /**
     * Sidebar menu definition.
     *
     * Struktur:
     *   'Group Label' => [
     *       ['label' => 'Submenu', 'route' => 'route.name'],
     *       ...
     *   ]
     *
     * Submenu yang route-nya tidak terdaftar otomatis di-skip (Route::has check).
     */
    $menus = [
        'Master Data' => [
            ['label' => 'Master Pasien',     'route' => 'master.pasien'],
            ['label' => 'Master Dokter',     'route' => 'master.dokter'],
            ['label' => 'Master Poli',       'route' => 'master.poli'],
            ['label' => 'Master Agama',      'route' => 'master.agama'],
            ['label' => 'Master Pendidikan', 'route' => 'master.pendidikan'],
            ['label' => 'Master Pekerjaan',  'route' => 'master.pekerjaan'],
            ['label' => 'Master Tipe Klaim', 'route' => 'master.klaim'],
            ['label' => 'Master Cara Masuk', 'route' => 'master.cara-masuk'],
            ['label' => 'Master Cara Keluar', 'route' => 'master.cara-keluar'],
            ['label' => 'Master Prosedur',    'route' => 'master.procedure'],
            ['label' => 'Master Parameter',   'route' => 'master.parameter'],
            ['label' => 'Master Kemasan',     'route' => 'master.kemasan'],
            ['label' => 'Master Jasa Dokter',     'route' => 'master.jasa-dokter'],
            ['label' => 'Master Jasa Karyawan',   'route' => 'master.jasa-karyawan'],
            ['label' => 'Master Jasa Paramedis',  'route' => 'master.jasa-paramedis'],
            ['label' => 'Master Diagnosa',   'route' => 'master.diagnosa'],
            ['label' => 'Master Radiologi',  'route' => 'master.radiologis'],
            ['label' => 'Master Lab',        'route' => 'master.laborat'],
            ['label' => 'Master Others',     'route' => 'master.others'],
            ['label' => 'Setup Jadwal BPJS', 'route' => 'master.setup-jadwal-bpjs'],
        ],
        'Rawat Jalan' => [
            ['label' => 'Daftar RJ',  'route' => 'rawat-jalan.daftar'],
            ['label' => 'Booking RJ', 'route' => 'rawat-jalan.booking'],
            ['label' => 'Antrian Apotek RJ', 'route' => 'transaksi.rj.antrian-apotek-rj'],
        ],
        'UGD' => [
            ['label' => 'Daftar UGD',         'route' => 'ugd.daftar'],
            ['label' => 'Antrian Apotek UGD', 'route' => 'transaksi.ugd.antrian-apotek-ugd'],
        ],
        'Apotek & Penunjang' => [
            ['label' => 'Apotek (RJ + UGD)', 'route' => 'transaksi.apotek'],
            ['label' => 'Laboratorium',      'route' => 'transaksi.penunjang.laborat'],
            ['label' => 'Penerimaan Medis',  'route' => 'gudang.penerimaan-medis'],
        ],
        'Master Toko/Apotek' => [
            ['label' => 'Master Kategori', 'route' => 'master.kategori'],
            ['label' => 'Master Satuan (UOM)', 'route' => 'master.uom'],
            ['label' => 'Master Kasir',    'route' => 'master.kasir'],
        ],
        'Keuangan' => [
            ['label' => 'Penerimaan Kas TU',  'route' => 'keuangan.penerimaan-kas-tu'],
            ['label' => 'Pengeluaran Kas TU', 'route' => 'keuangan.pengeluaran-kas-tu'],
        ],
        'Operasi' => [
            ['label' => 'Jadwal Operasi', 'route' => 'operasi.jadwal-operasi'],
        ],
        'Database Monitor' => [
            ['label' => 'Monitoring Dashboard', 'route' => 'database-monitor.monitoring-dashboard'],
            ['label' => 'Mount Control',        'route' => 'database-monitor.monitoring-mount-control'],
            ['label' => 'User Control',         'route' => 'database-monitor.user-control'],
            ['label' => 'Role Control',         'route' => 'database-monitor.role-control'],
        ],
    ];

    // Filter menu yang route-nya belum terdaftar
    $menus = collect($menus)
        ->map(fn ($subs) => array_values(array_filter($subs, fn ($s) => \Illuminate\Support\Facades\Route::has($s['route']))))
        ->filter(fn ($subs) => count($subs) > 0)
        ->all();
@endphp

<aside x-cloak id="app-sidebar"
    class="fixed top-20 left-0 z-[60] h-[calc(100vh-5rem)] w-80 max-w-[85vw]
           bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700
           transform transition-transform duration-300 ease-out"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    {{-- header sidebar --}}
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-2.5 w-2.5">
                <span class="absolute inline-flex w-full h-full rounded-full opacity-60 bg-brand-lime animate-ping"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-brand-green dark:bg-brand-lime"></span>
            </span>

            <div class="leading-tight">
                @auth
                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                    </div>
                @else
                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Guest</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Silakan login</div>
                @endauth
            </div>
        </div>
    </div>

    {{-- menu --}}
    <nav class="p-4 space-y-3 overflow-y-auto h-[calc(100vh-5rem-4rem)]">
        @foreach ($menus as $label => $subs)
            @php
                $key = Str::slug($label);
                $isGroupActive = collect($subs)->contains(fn ($s) => request()->routeIs($s['route']));
            @endphp

            <div class="overflow-hidden bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800"
                x-data="{ openOnLoad: @js($isGroupActive) }"
                x-init="if (openOnLoad) openMenus['{{ $key }}'] = true">

                {{-- Parent --}}
                <button type="button"
                    class="group flex items-center justify-between w-full px-4 py-2.5 rounded-xl
                           transition-colors duration-200
                           hover:bg-brand-green/10
                           dark:hover:bg-brand-lime/15"
                    x-on:click="toggleMenu('{{ $key }}')">

                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center transition-colors duration-200 rounded-lg w-7 h-7 bg-brand-green/10 text-brand-green group-hover:bg-brand-green group-hover:text-white dark:bg-brand-lime/15 dark:text-brand-lime dark:group-hover:bg-brand-lime dark:group-hover:text-slate-900">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 15L12 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M21.6359 12.9579L21.3572 14.8952C20.8697 18.2827 20.626 19.9764 19.451 20.9882C18.2759 22 16.5526 22 13.1061 22H10.8939C7.44737 22 5.72409 22 4.54903 20.9882C3.37396 19.9764 3.13025 18.2827 2.64284 14.8952L2.36407 12.9579C1.98463 10.3208 1.79491 9.00229 2.33537 7.87495C2.87583 6.7476 4.02619 6.06234 6.32691 4.69181L7.71175 3.86687C9.80104 2.62229 10.8457 2 12 2C13.1543 2 14.199 2.62229 16.2882 3.86687L17.6731 4.69181C19.9738 6.06234 21.1242 6.7476 21.6646 7.87495" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>

                        <span class="font-medium text-gray-800 transition-colors duration-200 text-md group-hover:text-brand-green dark:text-gray-100 dark:group-hover:text-brand-lime">
                            {{ $label }}
                        </span>
                    </div>

                    <svg class="w-4 h-4 text-gray-400 transition-colors duration-200 group-hover:text-brand-green dark:text-gray-500 dark:group-hover:text-brand-lime"
                        :class="openMenus['{{ $key }}'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 10 6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>

                {{-- Children --}}
                <div x-cloak x-show="openMenus['{{ $key }}']" x-collapse class="px-4 pb-3">
                    <div class="pt-2 space-y-1">
                        @foreach ($subs as $sub)
                            @php $isActive = request()->routeIs($sub['route']); @endphp
                            <a wire:navigate href="{{ route($sub['route']) }}"
                                @class([
                                    'block px-3 py-1.5 text-md rounded-md transition-colors duration-200',
                                    'bg-brand-green/15 text-brand-green dark:bg-brand-lime/20 dark:text-brand-lime font-semibold' => $isActive,
                                    'text-gray-700 hover:bg-brand-green/10 hover:text-brand-green dark:text-gray-300 dark:hover:bg-brand-lime/15 dark:hover:text-brand-lime' => !$isActive,
                                ])>
                                {{ $sub['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        @if (empty($menus))
            <div class="p-4 text-sm text-center text-gray-500 dark:text-gray-400">
                Belum ada menu yang tersedia.
            </div>
        @endif
    </nav>
</aside>
