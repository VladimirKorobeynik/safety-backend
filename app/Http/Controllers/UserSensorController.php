<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSensor;
use App\Models\UserSensorStatistic;
use App\Models\User;
use App\Http\Resources\UserSensorsResource;
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

    public function getUserSensors($user_id)
    {
        if (User::find($user_id)) {
            $userSensors = UserSensorsResource::collection(
                $this->convertUserSensorsWithSmartDevice(UserSensor::where('user_id', $user_id)
                ->where('is_activated', 1)
                ->get()
            ));
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
            foreach ($request as $sensor) {
                if ($sensor->smart_device_id === $id) {
                    array_push($smartDeviceWithSensors["sensors"], $sensor);
                }
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
        $sensor = UserSensor::where('user_sensor_id', $user_sensor_id)->get()->first();

        if ($sensor != null) {
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
}
