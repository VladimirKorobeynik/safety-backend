<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmartDevice;
use App\Models\UserSensor;
use App\Models\User;
use Carbon\Carbon;
use App\Traits\ApiHelper;
use App\Http\Controllers\MailController;
use Mail;

class SmartDeviceController extends Controller
{
    use ApiHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->onSuccess(SmartDevice::all(), '');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (count(SmartDevice::where('smart_device_number', $request->smart_device_number)->get()) == 0) {
            SmartDevice::create([
                'smart_device_number' => $request->smart_device_number,
                'count_gas_sensors' => 0,
                'count_humidity_sensors' => 0,
                'count_smoke_sensors' => 0,
                'count_motion_sensors' => 0,
                'is_bought' => 0,
                'is_activated' => 0
            ]);
            return $this->onSuccess('', 'Smart device added successfully');
        }
        return $this->onError(400, 'Bad request');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($smart_devices)
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
    public function update(Request $request, $smt_dev_id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($smt_dev_id)
    {
        if (SmartDevice::find($smt_dev_id)) {
            SmartDevice::where('smt_dev_id', $smt_dev_id)->delete();
            return $this->onSuccess('', 'Smart device deleted');
        }
        return $this->onError(404, 'This smart device not found');
    }

    public function activateSmartDevice(Request $request) 
    {
        $smartDevice = SmartDevice::where('smart_device_number', $request->smart_device_number)
        ->where('is_bought', true)->get()->first();

        if ($smartDevice != null) {

            if ($smartDevice->is_activated == 1) {
                return $this->onError(400, 'This smart device is already activated');
            }

            $smartDevice->update([
                'is_activated' => 1
            ]);

            $activation_key = $this->generateUuid(30);

            for ($i=0; $i < $smartDevice->count_gas_sensors; $i++) { 
                $this->createSensor(1, $request->user_id, $smartDevice->smart_device_number, $activation_key);
            }

            for ($i=0; $i < $smartDevice->count_humidity_sensors; $i++) { 
                $this->createSensor(2, $request->user_id, $smartDevice->smart_device_number, $activation_key);
            }

            for ($i=0; $i < $smartDevice->count_smoke_sensors; $i++) { 
                $this->createSensor(3, $request->user_id, $smartDevice->smart_device_number, $activation_key);
            }

            for ($i=0; $i < $smartDevice->count_motion_sensors; $i++) { 
                $this->createSensor(4, $request->user_id, $smartDevice->smart_device_number, $activation_key);
            }

            $user = User::where('user_id', $request->user_id)->get()->first();

            MailController::sendEmail($user->email, $user->name, $request->smart_device_number, $activation_key);

            return $this->onSuccess('', 'Smart device activated');
        }
        return $this->onError(404, 'This smart device not found');
    }

    public function generateUuid($length)
    {
        $uuid = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet);
   
       for ($i=0; $i < $length; $i++) {
           $uuid .= $codeAlphabet[random_int(0, $max-1)];
       }
   
       return $uuid;
   }

   public function createSensor($sensor_id, $user_id, $smart_device_number, $activation_key) 
   {
        UserSensor::create([
            'sensor_id' => $sensor_id,
            'house_id' => null,
            'user_id' => $user_id,
            'name' => 'sensor',
            'value' => '',
            'smart_device_id' => $smart_device_number,
            'is_active' => 1,
            'is_activated' => 0,
            'activate_date' => Carbon::now(),
            'activation_key' => $activation_key
        ]);
   }

   public function buySmartDevice(Request $request)
   {    
        $smartDev = SmartDevice::where('smart_device_number', $request->smart_device_number)->get()->first();
        if ($smartDev != null) {
            SmartDevice::where('smart_device_number', $request->smart_device_number)->update([
                'count_gas_sensors' => $request->count_gas_sensors,
                'count_humidity_sensors' => $request->count_humidity_sensors,
                'count_smoke_sensors' => $request->count_smoke_sensors,
                'count_motion_sensors' => $request->count_motion_sensors,
                'is_bought' => 1
            ]);
            return $this->onSuccess('', 'Smart device bought');
        }
        return $this->onError(404, 'This smart device not found');
   }
}
