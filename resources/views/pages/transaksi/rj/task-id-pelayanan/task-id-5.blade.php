<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Traits\Txn\Rj\EmrRJTrait;

new class extends Component {
    use EmrRJTrait;

    public ?int $rjNo = null;
    public bool $isLoading = false;

    /* ===============================
     | PROSES TASK ID 5 (Panggil Antrian)
     |
     | Alur:
     | 1. Guard rjNo + data kosong + noBooking + taskId4 prerequisite
     | 2. Set taskId5 timestamp jika belum ada
     | 3. Push ke BPJS jika poli spesialis — DI LUAR transaksi (API call)
     | 4. lockRJRow + patch hanya key taskIdPelayanan — atomik
    =============================== */
    public function prosesTaskId5(): void
    {
        // 1. Guard: rjNo belum di-set
        if (empty($this->rjNo)) {
            $this->dispatch('toast', type: 'warning', message: 'Nomor RJ tidak boleh kosong', title: 'Peringatan');
            return;
        }

        $this->isLoading = true;

        try {
            // 2. Ambil data RJ — tanpa lock dulu, hanya untuk baca awal
            $data = $this->findDataRJ($this->rjNo);

            if (empty($data)) {
                $this->dispatch('toast', type: 'error', message: 'Data RJ tidak ditemukan', title: 'Error');
                return;
            }

            // 3. Validasi prerequisite: taskId4 harus sudah ada
            if (empty($data['taskIdPelayanan']['taskId4'] ?? null)) {
                $this->dispatch('toast', type: 'error', message: 'TaskId4 (Masuk Poli) harus dilakukan terlebih dahulu', title: 'Gagal');
                return;
            }

            // 4. Validasi noBooking
            $noBooking = $data['noBooking'] ?? null;
            if (empty($noBooking)) {
                $this->dispatch('toast', type: 'error', message: 'No Booking tidak ditemukan', title: 'Error');
                return;
            }

            // 5. Inisialisasi taskIdPelayanan jika belum ada
            $data['taskIdPelayanan'] ??= [];

            // 6. Notifikasi jika taskId5 sudah pernah tercatat
            if (!empty($data['taskIdPelayanan']['taskId5'])) {
                $this->dispatch('toast', type: 'warning', message: "TaskId5 sudah tercatat: {$data['taskIdPelayanan']['taskId5']}", title: 'Info');
            }

            // 7. Set taskId5 jika belum ada
            if (empty($data['taskIdPelayanan']['taskId5'])) {
                $data['taskIdPelayanan']['taskId5'] = Carbon::now(config('app.timezone'))->format('d/m/Y H:i:s');
            }

            // 8. Push ke BPJS jika poli spesialis — DI LUAR transaksi (API call)
            // 9. Simpan ke DB — lock + patch hanya key taskIdPelayanan
            DB::transaction(function () use ($data) {
                $this->lockRJRow($this->rjNo);

                // Re-fetch setelah lock — patch hanya key taskIdPelayanan
                $existingData = $this->findDataRJ($this->rjNo) ?? [];

                if (empty($existingData)) {
                    throw new \RuntimeException('Data RJ tidak ditemukan saat akan disimpan.');
                }

                $existingData['taskIdPelayanan'] = $data['taskIdPelayanan'];
                $this->updateJsonRJ($this->rjNo, $existingData);
            });

            $this->dispatch('refresh-after-rj.saved');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage(), title: 'Error');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage(), title: 'Error');
        } finally {
            $this->isLoading = false;
        }
    }

    /* ===============================
     | HELPERS
     =============================== */};
?>

<div class="inline-block">
    <x-primary-button wire:click="prosesTaskId5" wire:loading.attr="disabled" wire:target="prosesTaskId5"
        class="!px-2 !py-1 text-xs" title="Klik untuk mencatat TaskId5 (Panggil Antrian)">
        <span wire:loading.remove wire:target="prosesTaskId5">
            TaskId5
        </span>
        <span wire:loading wire:target="prosesTaskId5">
            <x-loading />
        </span>
    </x-primary-button>
</div>
