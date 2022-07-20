<?php

namespace App\Exports;

use App\Models\User;
use App\Http\Resources\UserResource;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class UsersExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UserResource::collection(User::all());
    }

    public function headings(): array {
        return [
            '#',
            'Role',
            'Name',
            'Surname',
            'Number',
            'Address',
            'Email',
            'Birthday',
            'Active',
        ];
    }
       
    public function columnFormats(): array
    {
        return [
            'B' => 'string',
        ];
        
    }
}
