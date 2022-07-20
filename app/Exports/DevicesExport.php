<?php

namespace App\Exports;

use App\Models\SmartDevice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class DevicesExport implements FromCollection, WithHeadings, WithColumnFormatting, WithStrictNullComparison
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return SmartDevice::all();
    }

    public function headings(): array {
        return [
            '#',
            'Serial number',
            'Gas Sensors',
            'Humidity Sensors',
            'Smoke Sensors',
            'Motion Sensors',
            'Bought',
            'Activated'
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
