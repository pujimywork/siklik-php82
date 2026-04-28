<?php

namespace App\Http\Traits;


trait customErrorMessagesTrait
{
    public static function messages(): array
    {
        $messages = array(
            'required' => 'Data :attribute tidak boleh kosong.', //data tdak boleh kosong
            'digits' => 'Data :attribute harus berisi :digits digit.', //untuk angka harus berisi a length
            'digits_between' => 'Data :attribute harus berisi antara :min-:max digit.', //untuk angka  antara a,b length
            'min' => 'Data berisi minimal :min digit.', //untuk angka /character min length
            'max' => 'Data berisi maximl :max digit.', //untuk angka /character max length
            'exists' => 'Data :attribute tidak ada didalam data master', // mencari referensi pada table tertentu
            'date_format' => 'Format :attribute tgl :format', //format tgl dd/mm/yyyy
            'numeric' => 'Format :attribute harus berupa angka.', //data berupa angka
            'unique' => 'Data :attribute sudah terdaftar pada di dalam master.' //data berupa angka

        );

        return $messages;
    }
}
