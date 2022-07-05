<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\NotificationController;
use \App\Http\Controllers\HouseController;
use \App\Http\Controllers\UserSensorController;
use \App\Http\Controllers\RegisterController;
use \App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Auth
Route::post('/signUp', [RegisterController::class, 'signUp']);
Route::post('/signIn', [AuthController::class, 'signIn']);


Route::middleware(['auth:api', 'role'])->group(function() {
    //Auth
    Route::middleware(['scope:User,Admin,Operator'])->post('/logout', [AuthController::class, 'logOut']);

    //User
    Route::middleware(['scope:Admin'])->get('/allUsers', [UserController::class, 'index']);
    Route::middleware(['scope:Admin'])->post('/createUser', [UserController::class, 'store']);
    Route::middleware(['scope:Admin'])->put('/updateUser/{user_id}', [UserController::class, 'update']);
    Route::middleware(['scope:Admin'])->get('/getUser/{user_id}', [UserController::class, 'show']);
    Route::middleware(['scope:Admin'])->delete('/deleteUser/{user_id}', [UserController::class, 'destroy']);
    Route::middleware(['scope:Admin,User,Operator'])->post('/updateUserPassword/{user_id}', [UserController::class, 'updateUserPassword']);

    //Notification
    Route::middleware(['scope:Admin'])->get('/allNotifications', [NotificationController::class, 'index']);
    Route::middleware(['scope:Admin'])->post('/createNotification', [NotificationController::class, 'store']);
    Route::middleware(['scope:Admin'])->put('/updateNotification/{notification_id}', [NotificationController::class, 'update']);
    Route::middleware(['scope:Admin'])->get('/getNotification/{notification_id}', [NotificationController::class, 'show']);
    Route::middleware(['scope:Admin,User'])->delete('/deleteNotification/{notification_id}', [NotificationController::class, 'destroy']);
    Route::middleware(['scope:User'])->get('/getUserNotifications/{user_id}', [NotificationController::class, 'getUserNotifications']);

    //House
    Route::middleware(['scope:Admin'])->get('/allHouses', [HouseController::class, 'index']);
    Route::middleware(['scope:Admin,User'])->post('/createHouse', [HouseController::class, 'store']);
    Route::middleware(['scope:Admin,User'])->put('/updateHouse/{house_id}', [HouseController::class, 'update']);
    Route::middleware(['scope:Admin,User'])->get('/getHouse/{house_id}', [HouseController::class, 'show']);
    Route::middleware(['scope:Admin,User'])->delete('/deleteHouse/{house_id}', [HouseController::class, 'destroy']);
    Route::middleware(['scope:User'])->get('/getUserHouses/{user_id}', [HouseController::class, 'getUserHouses']);

    //UserSensor
    Route::middleware(['scope:Admin'])->get('/allUsersSensors', [UserSensorController::class, 'index']);
    Route::middleware(['scope:User'])->post('/createUserSensor', [UserSensorController::class, 'store']);
    Route::middleware(['scope:User'])->put('/updateUserSensor/{user_sensor_id}', [UserSensorController::class, 'update']);
    Route::middleware(['scope:User'])->get('/getUserSensor/{user_sensor_id}', [UserSensorController::class, 'show']);
    Route::middleware(['scope:Admin,User'])->delete('/deleteUserSensor/{user_sensor_id}', [UserSensorController::class, 'destroy']);
    Route::middleware(['scope:User'])->get('/getUserSensors/{user_sensor_id}', [UserSensorController::class, 'getUserSensors']);
    Route::middleware(['scope:User'])->put('/activateUserSensor', [UserSensorController::class, 'activateUserSensor']);
});