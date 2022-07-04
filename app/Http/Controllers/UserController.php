<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiHelper;
use Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->onSuccess(UserResource::collection(User::all()), '');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'surname' => ['required'],
            'number' => ['required', 'min:12', 'numeric'],
            'address' => ['required'],
            'email' => ['required', 'email'],
            'birthday' => ['required'],
            'login' => ['required'],
            'password' => ['required', 'min:8'],
        ]);

        if($validator->fails()) {
            return $this->onError(400, $validator->errors()->first());
        }

        if (count(User::where('email', $request->email)->get()) === 0) {
            if (count(User::where('login', $request->login)->get()) === 0) {
                $user = User::create([
                    'role_id' => 1,
                    'name' => $request->input('name'),
                    'surname' => $request->input('surname'),
                    'number' => $request->input('number'),
                    'address' => $request->input('address'),
                    'email' => $request->input('email'),
                    'birthday' => $request->input('birthday'),
                    'login' => $request->input('login'),
                    'is_active' => true,
                    'password' => Hash::make($request->input('password')),
                ]);
        
                return $this->onSuccess(new UserResource($user), 'User created');
            } else {
                return $this->onError(400, "This login is already taken");
            }
        } else {
            return $this->onError(400, "This email is already taken");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($user_id)
    {
        if (!empty(User::find($user_id))) {
            $user = new UserResource(User::find($user_id));
            return $this->onSuccess($user, 'User found');
        }
        return $this->onError(404, 'This user not found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user_id)
    {
        if (!empty(User::find($user_id))) {
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
                'surname' => ['required'],
                'number' => ['required', 'min:12', 'numeric'],
                'address' => ['required'],
                'email' => ['required', 'email'],
                'birthday' => ['required'],
                'login' => ['required'],
                'password' => ['min:8'],
            ]);

            if ($validator->fails()) {
                return $this->onError(400, $validator->errors()->first());
            }

            $userEmail = User::where('user_id', $user_id)->get('email')->first();
            $userlogin = User::where('user_id', $user_id)->get('login')->first();

            if (count(User::where('email', $request->email)->where('email', '!=' , $userEmail->email)->get()) === 0) {
                if (count(User::where('login', $request->login)->where('login', '!=' , $userlogin->login)->get()) === 0) {
                    $updatedUser = User::find($user_id)->update([
                        'name' => $request->name,
                        'surname' => $request->surname,
                        'number' => $request->number,
                        'address' => $request->address,
                        'email' => $request->email,
                        'birthday' => $request->birthday,
                        'login' => $request->login,
                        'is_active' => true,
                        'password' => Hash::make($request->password),
                    ]);
                    return $this->onSuccess($updatedUser, 'User updated');
                } else {
                    return $this->onError(400, 'This login is already taken');
                }
            } else {
                return $this->onError(400, 'This email is already taken');
            }
        }
        return $this->onError(404, 'This user not found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($user_id)
    {
        if (User::find($user_id)) {
            User::where('user_id', $user_id)->delete();
            return $this->onSuccess('', 'User deleted');
        }
        return $this->onError(404, 'This user not found');
    }
}
