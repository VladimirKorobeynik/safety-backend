<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserSensor;
use App\Traits\ApiHelper;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use ApiHelper;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
            $users;
            if ($request->searchString != '') {
                $users = User::orWhereRaw("concat(name, ' ', surname) like '" . $request->searchString . "%' ")->get();
            } else {
                $users = User::all();
            }
        return $this->onSuccess(UserResource::collection($users), '');
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
            'role_id' => ['required'],
            'name' => ['required'],
            'surname' => ['required'],
            'number' => ['required', 'min:12', 'numeric'],
            'address' => ['required'],
            'email' => ['required', 'email'],
            'birthday' => ['required'],
            'password' => ['required', 'min:8'],
        ]);

        if($validator->fails()) {
            return $this->onError(400, $validator->errors()->first());
        }

        if (count(User::where('email', $request->email)->get()) === 0) {
            $user = User::create([
                'role_id' => $request->input('role_id'),
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'number' => $request->input('number'),
                'address' => $request->input('address'),
                'email' => $request->input('email'),
                'birthday' => $request->input('birthday'),
                'is_active' => $request->input('is_active'),
                'password' => Hash::make($request->input('password')),
            ]);
    
            return $this->onSuccess(new UserResource($user), 'User created');
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
                'number' => ['min:12', 'numeric'],
                'address',
                'email' => ['required', 'email'],
                'birthday',
                'is_active'
            ]);

            if ($validator->fails()) {
                return $this->onError(400, $validator->errors()->first());
            }

            $userEmail = User::where('user_id', $user_id)->get('email')->first();

            if (count(User::where('email', $request->email)->where('email', '!=' , $userEmail->email)->get()) === 0) {
                $updatedUser = User::find($user_id)->update([
                    'name' => $request->name,
                    'surname' => $request->surname,
                    'number' => $request->number,
                    'address' => $request->address,
                    'email' => $request->email,
                    'birthday' => $request->birthday,
                    'is_active' => $request->is_active,
                ]);
                return $this->onSuccess($updatedUser, 'User updated');
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
            UserSensor::where('user_id', $user_id)->update(['house_id' => null]);
            User::where('user_id', $user_id)->delete();

            return $this->onSuccess('', 'User deleted');
        }
        return $this->onError(404, 'This user not found');
    }

    public function updateUserPassword(Request $request, $user_id) {
        $user = User::where('user_id', $user_id)->get()->first();

        if ($user != null) {
            if (Hash::check($request->old_password, $user->password)) {
                $user->update([
                    'password' => Hash::make($request->new_password)
                ]);

                return $this->onSuccess('', 'Password updated successfully!');
            }
            return $this->onError(400, 'Old password is not correct!');
        }

       return $this->onError(404, 'This user not found');
    }

    public function getRoles() {
        return $this->onSuccess(Role::get(), '');
    }

    public function exportUsers() {
        return (new UsersExport)->download('users.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
