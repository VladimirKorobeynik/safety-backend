<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSensor;
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
        ->where('smart_device_id', $request->smart_device_id)->get();

        if (count($unactivatedUserSensors) != 0) {
            $activated = UserSensor::where('activation_key', $request->activation_key)
            ->where('smart_device_id', $request->smart_device_id)->update(['is_activated' => 1]);
            return $this->onSuccess($activated, 'Activated success');
        }
        return $this->onError(400, 'Invalid activation key or smart device number');
    }
}
