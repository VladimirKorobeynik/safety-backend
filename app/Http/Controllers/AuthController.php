<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiHelper;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Validator;

class AuthController extends Controller
{
    use ApiHelper;

    public function signIn(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        if ($validator->fails()) {
            return $this->onError(400, $validator->errors()->first());
        }

        $user = User::where('email', $request->email)->get()->first();

        if ($user != null && Hash::check($request->password, $user->password)) {
            $userRole = Role::where('role_id', $user->role_id)->select('name')->get()->first();
            $successRegister['token'] = $user->createToken('Access token', [$userRole->name])->accessToken;
            $successRegister['name'] = $user->name;

            return $this->onSuccess($successRegister, 'Sign in successfully');
        }  
        return $this->onError(401, "Unauthorized");
    }

    public function logOut(Request $request) {
        $request->user()->token()->revoke();
        return $this->onSuccess('', 'You have successfully logout');
    }
}
