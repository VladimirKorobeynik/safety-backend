<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\House;
use \App\Models\User;
use App\Traits\ApiHelper;

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
            $house = House::create([
                'user_id' => $request->input('user_id'),
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'count_rooms' => $request->input('count_rooms'),
                'count_windows' => $request->input('count_windows'),
                'count_doors' => $request->input('count_doors'),
            ]);
    
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
            House::where('house_id', $house_id)->delete();
            return $this->onSuccess('', 'House deleted');
        }
        return $this->onError(404, 'This house not found');
    }

    public function getUserHouses($user_id) {
        if (!empty(User::find($user_id))) {
            $houses = House::where('user_id', $user_id)->get();
            return $this->onSuccess($houses, '');
        }
        return $this->onError(404, 'This user not found');
    }
}
