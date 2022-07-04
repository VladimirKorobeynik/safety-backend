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
Route::post('/logout', [AuthController::class, 'logout']);


//User
Route::get('/allUsers', [UserController::class, 'index']);
Route::post('/createUser', [UserController::class, 'store']);
Route::put('/updateUser/{user_id}', [UserController::class, 'update']);
Route::get('/getUser/{user_id}', [UserController::class, 'show']);
Route::delete('/deleteUser/{user_id}', [UserController::class, 'destroy']);

//Notification
Route::get('/allNotifications', [NotificationController::class, 'index']);
Route::post('/createNotification', [NotificationController::class, 'store']);
Route::put('/updateNotification/{notification_id}', [NotificationController::class, 'update']);
Route::get('/getNotification/{notification_id}', [NotificationController::class, 'show']);
Route::delete('/deleteNotification/{notification_id}', [NotificationController::class, 'destroy']);
Route::get('/getUserNotifications/{user_id}', [NotificationController::class, 'getUserNotifications']);

//House
Route::get('/allHouses', [HouseController::class, 'index']);
Route::post('/createHouse', [HouseController::class, 'store']);
Route::put('/updateHouse/{house_id}', [HouseController::class, 'update']);
Route::get('/getHouse/{house_id}', [HouseController::class, 'show']);
Route::delete('/deleteHouse/{house_id}', [HouseController::class, 'destroy']);
Route::get('/getUserHouses/{user_id}', [HouseController::class, 'getUserHouses']);

//UserSensor
Route::get('/allUsersSensors', [UserSensorController::class, 'index']);
Route::post('/createUserSensor', [UserSensorController::class, 'store']);
Route::put('/updateUserSensor/{user_sensor_id}', [UserSensorController::class, 'update']);
Route::get('/getUserSensor/{user_sensor_id}', [UserSensorController::class, 'show']);
Route::delete('/deleteUserSensor/{user_sensor_id}', [UserSensorController::class, 'destroy']);
Route::get('/getUserSensors/{user_sensor_id}', [UserSensorController::class, 'getUserSensors']);
Route::put('/activateUserSensor', [UserSensorController::class, 'activateUserSensor']);