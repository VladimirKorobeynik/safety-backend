<?php

namespace App\Exports;

use Illuminate\Http\Request;
use App\Models\House;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Http\Resources\HouseOwnerFullnameResource;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class HousesExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return HouseOwnerFullnameResource::collection(House::all());
    }

    public function headings(): array {
        return [
            '#',
            'user ID',
            'Fullname',
            'Name',
            'Address',
            'Rooms',
            'Windows',
            'Doors'
        ];
    }
}
