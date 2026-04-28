<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Traits\Txn\Rj\EmrRJTrait;

new class extends Component {
    use EmrRJTrait;

    public ?int $rjNo = null;
    public bool $isLoading = false;

    /**
     * Cek apakah poli spesialis
     */
    /**
     * Proses Get TaskId Antrean dari BPJS
     */
    public function prosesTaskidAntrean()
    {
        if (empty($this->rjNo)) {
            $this->dispatch('toast', type: 'warning', message: 'Nomor RJ tidak boleh kosong', title: 'Peringatan');
            return;
        }

        $this->isLoading = true;

        try {
            $data = $this->findDataRJ($this->rjNo);
            if (empty($data)) {
                $this->dispatch('toast', type: 'error', message: 'Data RJ tidak ditemukan', title: 'Error');
                return;
            }

            // Dapatkan noBooking
            $noBooking = $data['noBooking'] ?? null;

            if (empty($noBooking)) {
                $this->dispatch('toast', type: 'error', message: 'No Booking tidak ditemukan', title: 'Error');
                return;
            }

            // Ambil task list dari BPJS jika poli spesialis
            $this->dispatch('refresh-after-rj.saved');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage(), title: 'Error');
        } finally {
            $this->isLoading = false;
        }
    }
};
?>

<div class="inline-block">
    <x-primary-button wire:click="prosesTaskidAntrean" wire:loading.attr="disabled" wire:target="prosesTaskidAntrean"
        class="!px-2 !py-1 text-xs" title="Klik untuk mengambil TaskId Antrean dari BPJS">

        <span wire:loading.remove wire:target="prosesTaskidAntrean">
            TaskId Antrean
        </span>

        <span wire:loading wire:target="prosesTaskidAntrean">
            <x-loading />
        </span>
    </x-primary-button>
</div>
