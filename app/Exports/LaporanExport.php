<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class LaporanExport implements FromCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }
}
