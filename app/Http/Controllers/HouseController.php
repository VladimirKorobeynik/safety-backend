<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use \App\Models\User;
use \App\Models\UserSensor;
use \App\Models\UserSensorStatistic;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HousesExport;
use \App\Models\Script;
use App\Traits\ApiHelper;
use Illuminate\Support\Facades\DB;
use Validator;

class HouseController extends Controller
{
    use ApiHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $houses;
        if ($request->searchString != '') {
            $houses = House::where('name', 'like', '%' . $request->searchString . '%')->get();
        } else {
            $houses = House::all();
        }

        foreach ($houses as $house) {
           $user = User::where('user_id', $house->user_id)->select('name', 'surname')->get()->first();
           $house->user_fullname = $user->name . ' ' . $user->surname; 
        }
        return $this->onSuccess($houses, '');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!empty(User::find($request->input('user_id')))) {
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'address' => ['required'],
                'count_rooms',
                'count_windows',
                'count_doors',
            ]);

            if ($validator->fails()) {
                return $this->onError(400, $validator->errors()->first());
            }

            $operators = User::where('role_id', 3)->get('user_id');
            $operatorId = 0;

            $userHouses = House::where('user_id', $request->input('user_id'))->get();

            if (count($userHouses) == 0) {
                $operatorId = $operators[rand(0, count($operators) - 1)]->user_id;
            } else {
                $operatorId = DB::table('operator_responsibility_areas')->where('house_id', $userHouses[0]->house_id)->get()->first()->user_id;
            }

            $house = House::create([
                'user_id' => $request->input('user_id'),
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'count_rooms' => $request->input('count_rooms'),
                'count_windows' => $request->input('count_windows'),
                'count_doors' => $request->input('count_doors'),
            ]);

            DB::table('operator_responsibility_areas')->insert(array(
                'user_id' => $operatorId,
                'house_id' => $house->house_id
            ));

            $operatorObj = User::where('user_id', $operatorId)->get()->first();

            $operatorInfoObj = (object) array(
                'name' => $operatorObj->name,
                'surname' => $operatorObj->surname,
                'email' => $operatorObj->email,
                'number' => $operatorObj->number
            );

            $house->operator_info = $operatorInfoObj;

            return $this->onSuccess($house, 'Created house');
        }
        return $this->onError(400, 'Bad request');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($house_id)
    {
        if (!empty(House::find($house_id))) {
            $house = House::find($house_id);
            return $this->onSuccess($house, 'House found');
        }
        return $this->onError(404, 'This house not found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $house_id)
    {
        if (!empty(House::find($house_id))) {
            $updatedHouse = House::find($house_id)->update([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'address' => $request->address,
                'count_rooms' => $request->count_rooms,
                'count_windows' => $request->count_windows,
                'count_doors' => $request->count_doors,
            ]);
            return $this->onSuccess($updatedHouse, 'House updated');
        }
        return $this->onError(404, 'This house not found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($house_id)
    {
        if (House::find($house_id)) {
            UserSensor::where('house_id', $house_id)->update([
                'house_id' => null
            ]);
            House::where('house_id', $house_id)->delete();
            return $this->onSuccess('', 'House deleted');
        }
        return $this->onError(404, 'This house not found');
    }

    public function getUserHouses($user_id) 
    {
        if (!empty(User::find($user_id))) {
            $houses = House::where('user_id', $user_id)->get();
            return $this->onSuccess($houses, '');
        }
        return $this->onError(404, 'This user not found');
    }

    public function getOperatorResponsibilityHouse($user_id, Request $request) 
    {
        if (!empty(User::find($user_id))) {
            $operatorHouses = DB::table('operator_responsibility_areas')->where('user_id', $user_id)->get('house_id');
            $houses = [];

            if (count($operatorHouses) != 0) {
                $housesId = [];
                $usersOperatorResponse = [];

                foreach ($operatorHouses as $value) {
                    array_push($housesId, $value->house_id);
                }
                $houses = House::whereIn('house_id', $housesId)->get();

                foreach ($houses as $value) {
                    if (!in_array($value->user_id, $usersOperatorResponse)) {
                        array_push($usersOperatorResponse, $value->user_id);
                    }
                }

                $operatorHouses = [];

                foreach ($usersOperatorResponse as $value) {
                    $userWithHouse = new class{
                        public $user_id = 0;
                        public $user_fullname = '';
                        public $user_email = '';
                        public $houses = [];
                    };
                    $user = User::where('user_id', $value)->get()->first();
                    $userWithHouse->houses = $this->getUserHousesWithStatistics($value, $request)->original['data'];
                    $userWithHouse->user_id = $user->user_id;
                    $userWithHouse->user_fullname = $user->name . ' ' . $user->surname;
                    $userWithHouse->user_email = $user->email;
                    array_push($operatorHouses, $userWithHouse);
                }

                return $this->onSuccess($operatorHouses, '');
            }
            return $this->onSuccess($houses, '');
        }
        return $this->onError(404, 'This user not found');
    }

    public function bindSmartDeviceToHouse(Request $request)
    {
        $smartDeviceSensors = UserSensor::where('smart_device_id', $request->smart_device_id)->get();
        $house = House::where('house_id', $request->house_id)->get()->first();
        
        if (count($smartDeviceSensors) != 0 && $house != null) {
            $oldSensors = UserSensor::where('house_id', $request->house_id)->get();
            if (count($oldSensors) != 0) {
                foreach ($oldSensors as $value) {
                    $value->update(['house_id' => null]);
                }
            }
            foreach ($smartDeviceSensors as $sensor) {
                $sensor->update([
                    'house_id' => $request->house_id
                ]);
            }
            return $this->onSuccess('', 'Binded successfully');
        }
        return $this->onError(400, 'Bad request');
    }

    public function getHouseScripts($house_id)
    {
        $houseScripts = Script::where('house_id', $house_id)->get();
        return $this->onSuccess($houseScripts, '');
    }

    public function getUserHousesWithStatistics($user_id, Request $request) {
        if (User::find($user_id)) {
            $userHouses;
            if ($request->searchString != '') {
                $userHouses = House::where('user_id', $user_id)->where('name', 'like', '%' . $request->searchString . '%')->get();
            } else {
                $userHouses = House::where('user_id', $user_id)->get();
            }
           $userSensors = UserSensor::where('user_id', $user_id)->get();

           foreach ($userHouses as $house) {
                $count_smoke_sensors = 0;
                $count_humidity_sensors = 0;
                $count_gas_sensors = 0;
                $count_motion_sensors = 0;

                $smoke_sensors = [];
                $humidity_sensors = [];
                $gas_sensors = [];
                $motion_sensors = [];

                $device_num = '';

                foreach ($userSensors as $sensor) {
                    if ($house->house_id == $sensor->house_id) {
                        $device_num = $sensor->smart_device_id;
                        switch ($sensor->sensor_id) {
                            case 1:
                                $count_gas_sensors++;
                                array_push($gas_sensors, $sensor->user_sensor_id);
                                break;
                            case 2:
                                $count_humidity_sensors++;
                                array_push($humidity_sensors, $sensor->user_sensor_id);
                                break;
                            case 3:
                                $count_smoke_sensors++;
                                array_push($smoke_sensors, $sensor->user_sensor_id);
                                break;
                            case 4:
                                $count_motion_sensors++;
                                array_push($motion_sensors, $sensor->user_sensor_id);
                                break;
                            default:
                                break;
                        }
                    } 
                }

                $statSmoke = [];
                foreach ($smoke_sensors as $user_sens_id) {
                    $count = count(UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->get());
                    if ($count > 12) {
                        array_push($statSmoke, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->skip($count - 12)->take(12)->get());
                    } else {
                        array_push($statSmoke, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->take($count)->get());
                    }
                }

                $statHumidity = [];
                foreach ($humidity_sensors as $user_sens_id) {
                    $count = count(UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->get());
                    if ($count > 12) {
                        array_push($statHumidity, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->skip($count - 12)->take(12)->get());
                    } else {
                        array_push($statHumidity, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->take($count)->get());
                    }
                }

                $statGas = [];
                foreach ($gas_sensors as $user_sens_id) {
                    $count = count(UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->get());
                    if ($count > 12) {
                        array_push($statGas, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->skip($count - 12)->take(12)->get());
                    } else {
                        array_push($statGas, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->take($count)->get());
                    }
                }

                $statMotion = [];
                foreach ($motion_sensors as $user_sens_id) {
                    $count = count(UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->get());
                    if ($count > 12) {
                        array_push($statMotion, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->skip($count - 12)->take(12)->get());
                    } else {
                        array_push($statMotion, UserSensorStatistic::where('user_sensor_id', $user_sens_id)->orderBy('created_at', 'ASC')->take($count)->get());
                    }
                }
                                
                $house['stat_smoke'] = $this->calculateAvarageStatistic($statSmoke);          
                $house['stat_humidity'] = $this->calculateAvarageStatistic($statHumidity); 
                $house['stat_gas'] = $this->calculateAvarageStatistic($statGas);
                $house['stat_motion'] = $this->calculateAvarageStatistic($statMotion);
                
                $isHouseHaveSmartDevice = false;

                foreach ($userSensors as $value) {
                    if ($value->house_id === $house->house_id)  {
                        $isHouseHaveSmartDevice = true;
                    }
                }
                

                $house['smart_device_id'] = ($isHouseHaveSmartDevice) ? $device_num : null;
                $house['count_smoke_sensors'] = $count_smoke_sensors;
                $house['count_humidity_sensors'] = $count_humidity_sensors;
                $house['count_gas_sensors'] = $count_gas_sensors;
                $house['count_motion_sensors'] = $count_motion_sensors;
           }
           return $this->onSuccess($userHouses, '');
        }
        return $this->onError(404, 'This user not found');
    }

    public function calculateAvarageStatistic($statArr) {
        $avarageStatArr = [];
        for ($i=0; $i < count($statArr); $i++) {
            for ($j=0; $j < count($statArr[$i]); $j++) { 
                $created_at_old = $statArr[$i][$j]->created_at;
                $sumVal = 0;
                for ($k=0; $k < count($statArr); $k++) { 
                    $created_at = $statArr[$k][$j]->created_at;
                    $value = (int) $statArr[$k][$j]->sensor_value;
                    if (mb_strcut($created_at_old, 0, 16) == mb_strcut($created_at, 0, 16)) {
                        $sumVal += $value;
                    }
                }
                array_push($avarageStatArr, $sumVal / count($statArr));
            }
            break;
        }
        return $avarageStatArr;
    }

    public function exportHouses() {
        return (new HousesExport)->download('houses.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
