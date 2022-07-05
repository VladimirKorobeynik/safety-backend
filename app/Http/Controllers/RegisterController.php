<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiHelper;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class RegisterController extends Controller
{
    use ApiHelper;

    public function signUp(Request $request) {

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

                $successRegister['token'] = $user->createToken('Access token', ['User'])->accessToken;
                $successRegister['name'] = $user->name;

                return $this->onSuccess($successRegister, 'User register successfully');
            } else {
                return $this->onError(400, "This login is already taken");
            }
        } else {
            return $this->onError(400, "This email is already taken");
        }
    }
}
