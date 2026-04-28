<?php
// resources/views/pages/transaksi/apotek/apotek.blade.php
// Wrapper Antrian Apotek — RJ only (klinik pratama, tidak ada UGD)

use Livewire\Component;

new class extends Component {
    public string $activeTab = 'rj';
};
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="px-4 py-4 mx-auto max-w-[1920px]">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Antrian Apotek</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Telaah resep &amp; pelayanan kefarmasian — Rawat Jalan
                </p>
            </div>
        </div>

        {{-- CONTENT — langsung antrian RJ --}}
        <div class="mt-4">
            <livewire:pages::transaksi.rj.antrian-apotek-rj.antrian-apotek-rj
                wire:key="antrian-apotek-rj-wrapper" />
        </div>

    </div>
</div>
