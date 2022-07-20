<?php

namespace App\Imports;

use App\Models\SmartDevice;
use Maatwebsite\Excel\Concerns\ToModel;


class DevicesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new SmartDevice([
            'smart_device_number' => $row[0],
            'count_gas_sensors' => 0,
            'count_humidity_sensors' => 0,
            'count_smoke_sensors' => 0,
            'count_motion_sensors' => 0,
            'is_bought' => false,
            'is_activated' => false,
        ]);
    }
}
