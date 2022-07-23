<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSensor;
use App\Models\UserSensorStatistic;
use App\Models\User;
use App\Models\House;
use App\Models\Notification;
use App\Http\Resources\UserSensorsResource;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiHelper;

class UserSensorController extends Controller
{
    use ApiHelper;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->onSuccess(UserSensorsResource::collection(UserSensor::all()), '');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getUserSensors($user_id, Request $request)
    {
        if (User::find($user_id)) {
            $userSensors;
            if ($request->searchString != '') {
                $userSensors = UserSensorsResource::collection(
                    $this->convertUserSensorsWithSmartDevice(UserSensor::where('user_id', $user_id)
                    ->where('is_activated', 1)
                    ->where('smart_device_id', 'like', '%' . $request->searchString . '%')
                    ->get()
                ));
            } else {
                $userSensors = UserSensorsResource::collection(
                    $this->convertUserSensorsWithSmartDevice(UserSensor::where('user_id', $user_id)
                    ->where('is_activated', 1)
                    ->get()
                ));
            }
           
            return $this->onSuccess($userSensors, '');
        }
        return $this->onError(404, 'This user not found');
    }

    public function convertUserSensorsWithSmartDevice($request)
    {
        $result = [];
        $smartDevicesId = [];

        foreach ($request as $sensor) {
            if (!in_array($sensor->smart_device_id, $smartDevicesId, true)) {
                array_push($smartDevicesId, $sensor->smart_device_id);
            }
        }

        foreach ($smartDevicesId as $id) {
            $smartDeviceWithSensors = [];
            $smartDeviceWithSensors["smart_device_id"] = $id;
            $smartDeviceWithSensors["sensors"] = [];
            $smartDeviceWithSensors["isBinded"] = false;
            foreach ($request as $sensor) {
                if ($sensor->smart_device_id === $id) {
                    array_push($smartDeviceWithSensors["sensors"], $sensor);
                }
            }
            $deviceSensorsCount = count($smartDeviceWithSensors["sensors"]);
            $bindedSensors = 0;

            $count_gas_sensors = 0;
            $count_humidity_sensors = 0;
            $count_smoke_sensors = 0;
            $count_motion_sensors = 0;

            foreach ($smartDeviceWithSensors["sensors"] as $sensor) {
                if ($sensor->house_id != null) {
                    $bindedSensors++;
                }

                switch ($sensor->sensor_id) {
                    case 1:
                        $count_gas_sensors++;
                        break;
                    case 2:
                        $count_humidity_sensors++;
                        break;
                    case 3:
                        $count_smoke_sensors++;
                        break;
                    case 4:
                        $count_motion_sensors++;
                        break;
                    default:
                        break;
                }
            }

            $smartDeviceWithSensors["count_gas_sensors"] = $count_gas_sensors;
            $smartDeviceWithSensors["count_humidity_sensors"] = $count_humidity_sensors;
            $smartDeviceWithSensors["count_smoke_sensors"] = $count_smoke_sensors;
            $smartDeviceWithSensors["count_motion_sensors"] = $count_motion_sensors;

            if ($deviceSensorsCount == $bindedSensors) {
                $smartDeviceWithSensors["isBinded"] = true;
            }
            array_push($result, $smartDeviceWithSensors);
        }

        return $result;
    }

    public function activateUserSensor(Request $request) 
    {
        $unactivatedUserSensors = UserSensor::where('activation_key', $request->activation_key)
        ->where('smart_device_id', $request->smart_device_id)->where('is_activated', 0)->get();

        if (count($unactivatedUserSensors) != 0) {


            $activated = UserSensor::where('activation_key', $request->activation_key)
            ->where('smart_device_id', $request->smart_device_id)->update(['is_activated' => 1]);
            return $this->onSuccess($activated, 'Activated success');
        }
        return $this->onError(400, 'Invalid activation key or smart device number');
    }

    public function updateSensorValue(Request $request) {
        $sensor = UserSensor::where('user_sensor_id', $request->user_sensor_id)->get()->first();

        if ($sensor != null) {

            $typeSensor = DB::table('sensor_types')->where('sensor_id', $sensor->sensor_id)->get()->first();
            $house = House::where('house_id', $sensor->house_id)->get()->first();
            $houseOperator = DB::table('operator_responsibility_areas')->where('house_id', $house->house_id)->get()->first();
            
            if ($request->value < $typeSensor->min_value) {
                //User
                Notification::create([
                    'user_id' => $sensor->user_id,
                    'title' => $typeSensor->type . ' sensor went off',
                    'content' => 'In house - '. $house->name . ' ' . $typeSensor->type . ' sensor with name (' . $sensor->name . ') below minimum current values ' . $request->value . ' < min(' . $typeSensor->min_value . ')',

                ]);
                $user = User::where('user_id', $sensor->user_id)->get()->first();
                //Operator
                Notification::create([
                    'user_id' => $houseOperator->user_id,
                    'title' => $typeSensor->type . ' sensor went off',
                    'content' => 'In house - '. $house->name . ' Owner (' . $user->name . ' ' . $user->surname . ') ' . $typeSensor->type . ' sensor with name (' . $sensor->name . ') below minimum current values ' . $request->value . ' < min(' . $typeSensor->min_value . ')',
                ]);
            }

            if ($request->value >= $typeSensor->max_value) {
                Notification::create([
                    'user_id' => $sensor->user_id,
                    'title' => $typeSensor->type . ' sensor went off',
                    'content' => 'In house - '. $house->name . ' ' . $typeSensor->type . ' sensor with name (' . $sensor->name . ') above maximum, current values ' . $request->value . ' > max(' . $typeSensor->max_value . ')' 
                ]);
                $user = User::where('user_id', $sensor->user_id)->get()->first();
                //Operator
                Notification::create([
                    'user_id' => $houseOperator->user_id,
                    'title' => $typeSensor->type . ' sensor went off',
                    'content' => 'In house - '. $house->name . ' Owner (' . $user->name . ' ' . $user->surname . ') ' . $typeSensor->type . ' sensor with name (' . $sensor->name . ') above maximum, current values ' . $request->value . ' > max(' . $typeSensor->max_value . ')' 
                ]);
            }

            $sensor->update([
                'value' => $request->value
            ]);

            UserSensorStatistic::create([
                'user_sensor_id' => $request->input('user_sensor_id'),
                'sensor_value' => $request->input('value')
            ]);

            return $this->onSuccess('', 'Updated sensor value successfully');
        }
        return $this->onError(404, 'This sensor not found');
    }

    public function getUserSensorStatistic($user_sensor_id) {
        $sensor = UserSensor::where('user_sensor_id', $user_sensor_id)->get()->first();

        if ($sensor != null) {
            $sensorStatistic = UserSensorStatistic::where('user_sensor_id', $user_sensor_id)->get();
            return $this->onSuccess($sensorStatistic, 'Received statistics successfully');
        }
        return $this->onError(404, 'This sensor not found');
    }

    public function getSensorsType() {
        return $this->onSuccess(DB::table('sensor_types')->get(), 'Sensors Types');
    }
}
