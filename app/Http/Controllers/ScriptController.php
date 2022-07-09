<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Script;
use \App\Models\UserSensor;
use \App\Models\House;
use \App\Models\ScriptSetting;
use App\Traits\ApiHelper;

class ScriptController extends Controller
{
    use ApiHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!empty(House::find($request->input('house_id')))) {
            $isActive = 0;
            if (count(Script::where('house_id', $request->house_id)->get()) == 0) {
                $isActive = 1;
            }

            $userSensors = UserSensor::where('house_id', $request->house_id)->get();

            if (count($userSensors) != 0) {
                $script = Script::create([
                    'house_id' => $request->input('house_id'),
                    'name' => $request->input('name'),
                    'is_active' => $isActive
                ]);
    
                foreach ($userSensors as $sensor) {
                    
                    ScriptSetting::create([
                        'script_id' => $script->script_id,
                        'user_sensor_id' => $sensor->user_sensor_id,
                        'is_active' => $sensor->is_active
                    ]);
                }
        
                return $this->onSuccess($script, 'Script created successfully');
            }
            return $this->onError(400, 'First bind your smar device to house and then create script');
        }
        return $this->onError(400, 'Bad request');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($script_id)
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
    public function update(Request $request, $script_id)
    {
        if (Script::find($script_id)) {
            Script::where('script_id', $script_id)->update([
                'name' => $request->name
            ]);

            foreach ($request->settings as $setting) {
                ScriptSetting::where('user_sensor_id', $setting['user_sensor_id'])->update([
                    'is_active' => $setting['is_active']
                ]);
            }
            return $this->onSuccess('', 'Script updated');
        }
        return $this->onError(404, 'This script not found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($script_id)
    {
        if (Script::find($script_id)) {
            Script::where('script_id', $script_id)->delete();
            return $this->onSuccess('', 'Script deleted');
        }
        return $this->onError(404, 'This script not found');
    }

    public function activateScript(Request $request) 
    {
        if (count(House::where('house_id', $request->house_id)->get()) != 0) {
            if (Script::where('script_id', $request->script_id)->get()->first() != null) {
                $sensorScripts = Script::where('house_id', $request->house_id)->get();

                foreach ($sensorScripts as $script) {
                    if ($script->script_id != $request->script_id) {
                        $script->update(["is_active" => 0]);
                        continue;
                    }
                    $script->update(["is_active" => 1]);
                }
        
                return $this->onSuccess('', 'Script selected successfully');
            }
        }
        return $this->onError(400, 'Bad request');
    }

    public function getScriptSetting($script_id) 
    {
        if (Script::find($script_id)) {
            $scriptSettings = ScriptSetting::where('script_id', $script_id)->get();
            return $this->onSuccess($scriptSettings, '');
        }
        return $this->onError(404, 'This script not found');
    }

    public function executeScript($script_id)
    {
        if (Script::find($script_id)) {
            $scriptSettings = ScriptSetting::where('script_id', $script_id)->get();
            
            foreach ($scriptSettings as $setting) {
                UserSensor::where('user_sensor_id', $setting['user_sensor_id'])->update([
                    'is_active' => $setting['is_active']
                ]);
            }
            return $this->onSuccess('', 'Script has been successfully executed');
        }
        return $this->onError(404, 'This script not found');
    }
}
