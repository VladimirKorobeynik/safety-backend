<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use \App\Models\User;
use \App\Models\UserSensor;
use \App\Models\Script;
use App\Traits\ApiHelper;
use Illuminate\Support\Facades\DB;

class HouseController extends Controller
{
    use ApiHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->onSuccess(House::all(), '');
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

    public function getOperatorResponsibilityHouse($user_id) 
    {
        if (!empty(User::find($user_id))) {
            $operatorHouses = DB::table('operator_responsibility_areas')->where('user_id', $user_id)->get('house_id');
            $houses = [];

            if (count($operatorHouses) != 0) {
                $housesId = [];

                foreach ($operatorHouses as $value) {
                    array_push($housesId, $value->house_id);
                }

                $houses = House::whereIn('house_id', $housesId)->get();
                return $this->onSuccess($houses, '');
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
}
