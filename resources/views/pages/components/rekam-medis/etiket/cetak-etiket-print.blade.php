<x-pdf.layout-etiket>

    {{-- HEADER --}}
    <table class="w-full pb-1 mb-1 border-b border-gray-400" cellpadding="0" cellspacing="0">
        <tr>
            <td class="pr-1 align-middle" style="width:auto;">
                <img src="{{ public_path('images/Logo Persegi.png') }}" alt="Logo RS" class="object-contain"
                    style="height:6mm; width:auto;">
            </td>
            <td class="text-left align-middle">
                <div class="font-bold text-gray-900" style="font-size:7pt;">RUMAH SAKIT ISLAM MADINAH</div>
            </td>
        </tr>
    </table>

    {{-- NO RM --}}
    <div class="mb-1">
        <span class="text-[9px] font-bold tracking-wide text-black">
            {{ $data['regNo'] ?? '-' }}
        </span>
    </div>

    {{-- INFO PASIEN --}}
    <table class="w-full" cellpadding="0" cellspacing="0">
        <tr>
            <td class="w-[10mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">Nama</td>
            <td class="w-[2.5mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">:</td>
            <td class="text-[8.5px] font-bold text-black align-top py-[0.2mm]">
                {{ $data['regName'] ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="w-[10mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">L/P</td>
            <td class="w-[2.5mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">:</td>
            <td class="text-[6.5px] text-gray-800 align-top py-[0.2mm]">
                {{ $data['jenisKelamin']['jenisKelaminDesc'] ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="w-[10mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">TTL</td>
            <td class="w-[2.5mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">:</td>
            <td class="text-[6.5px] text-gray-800 align-top py-[0.2mm]">
                {{ $data['tempatLahir'] ?? '-' }},
                {{ $data['tglLahir'] ?? '-' }}
                ({{ $data['thn'] ?? '-' }})
            </td>
        </tr>
        <tr>
            <td class="w-[10mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">Alamat</td>
            <td class="w-[2.5mm] text-[6.5px] text-gray-500 align-top py-[0.2mm]">:</td>
            <td class="text-[6.5px] text-gray-800 align-top py-[0.2mm]">
                @php
                    $alamat = $data['identitas']['alamat'] ?? '-';
                    $rt = $data['identitas']['rt'] ?? '';
                    $rw = $data['identitas']['rw'] ?? '';
                    $desa = $data['identitas']['desaName'] ?? '';
                    $kec = $data['identitas']['kecamatanName'] ?? '';
                    $full = trim(
                        $alamat .
                            ($rt ? ' RT ' . $rt : '') .
                            ($rw ? '/RW ' . $rw : '') .
                            ($desa ? ', ' . $desa : '') .
                            ($kec ? ', ' . $kec : ''),
                    );
                @endphp
                {{ \Illuminate\Support\Str::limit($full, 55) }}
            </td>
        </tr>
    </table>

    {{-- BARCODE --}}
    <div class="mt-1 text-center">
        @php $regNo = $data['regNo'] ?? '0'; @endphp
        {!! DNS1D::getBarcodeHTML($regNo, 'C39', 0.75, 16, 'black', false) !!}
    </div>

</x-pdf.layout-etiket>
