<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\NotificationController;
use \App\Http\Controllers\HouseController;
use \App\Http\Controllers\UserSensorController;
use \App\Http\Controllers\ScriptController;
use \App\Http\Controllers\RegisterController;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\SmartDeviceController;

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

//Smart Devices
Route::put('/buySmartDevice', [SmartDeviceController::class, 'buySmartDevice']);

//Sensor
Route::put('/updateSensorValue', [UserSensorController::class, 'updateSensorValue']);

Route::middleware(['auth:api', 'role'])->group(function() {
    //Auth
    Route::middleware(['scope:User,Admin,Operator'])->post('/logout', [AuthController::class, 'logOut']);

    //User
    Route::middleware(['scope:Admin'])->get('/allUsers', [UserController::class, 'index']);
    Route::middleware(['scope:Admin'])->post('/createUser', [UserController::class, 'store']);
    Route::middleware(['scope:Admin,User,Operator'])->put('/updateUser/{user_id}', [UserController::class, 'update']);
    Route::middleware(['scope:Admin,User,Operator'])->get('/getUser/{user_id}', [UserController::class, 'show']);
    Route::middleware(['scope:Admin,User,Operator'])->delete('/deleteUser/{user_id}', [UserController::class, 'destroy']);
    Route::middleware(['scope:Admin,User,Operator'])->put('/updateUserPassword/{user_id}', [UserController::class, 'updateUserPassword']);
    Route::middleware(['scope:Admin'])->get('/getRoles', [UserController::class, 'getRoles']);
    Route::middleware(['scope:Admin'])->get('/exportUsers', [UserController::class, 'exportUsers']);

    //Notification
    Route::middleware(['scope:Admin'])->get('/allNotifications', [NotificationController::class, 'index']);
    Route::middleware(['scope:Admin'])->post('/createNotification', [NotificationController::class, 'store']);
    Route::middleware(['scope:Admin'])->put('/updateNotification/{notification_id}', [NotificationController::class, 'update']);
    Route::middleware(['scope:Admin'])->get('/getNotification/{notification_id}', [NotificationController::class, 'show']);
    Route::middleware(['scope:Admin,User,Operator'])->delete('/deleteNotification/{notification_id}', [NotificationController::class, 'destroy']);
    Route::middleware(['scope:User,Operator'])->get('/getUserNotifications/{user_id}', [NotificationController::class, 'getUserNotifications']);

    //House
    Route::middleware(['scope:Admin'])->get('/allHouses', [HouseController::class, 'index']);
    Route::middleware(['scope:Admin,User'])->post('/createHouse', [HouseController::class, 'store']);
    Route::middleware(['scope:Admin,User'])->put('/updateHouse/{house_id}', [HouseController::class, 'update']);
    Route::middleware(['scope:Admin,User'])->get('/getHouse/{house_id}', [HouseController::class, 'show']);
    Route::middleware(['scope:Admin,User'])->delete('/deleteHouse/{house_id}', [HouseController::class, 'destroy']);
    Route::middleware(['scope:User'])->get('/getUserHouses/{user_id}', [HouseController::class, 'getUserHouses']);
    Route::middleware(['scope:Operator'])->get('/getOperatorResponsibilityHouses/{user_id}', [HouseController::class, 'getOperatorResponsibilityHouse']);
    Route::middleware(['scope:User'])->put('/bindSmartDeviceToHouse', [HouseController::class, 'bindSmartDeviceToHouse']);
    Route::middleware(['scope:User'])->get('/getHouseScripts/{house_id}', [HouseController::class, 'getHouseScripts']);
    Route::middleware(['scope:User'])->get('/getUserHousesWithStatistics/{user_id}', [HouseController::class, 'getUserHousesWithStatistics']);
    Route::middleware(['scope:Admin'])->get('/exportHouses', [HouseController::class, 'exportHouses']);

    //User Sensor
    Route::middleware(['scope:Admin'])->get('/allUsersSensors', [UserSensorController::class, 'index']);
    Route::middleware(['scope:User'])->get('/getUserSensors/{user_id}', [UserSensorController::class, 'getUserSensors']);
    Route::middleware(['scope:User'])->put('/activateUserSensor', [UserSensorController::class, 'activateUserSensor']);
    Route::middleware(['scope:User'])->get('/getUserSensorStatistic/{user_sensor_id}', [UserSensorController::class, 'getUserSensorStatistic']);
    Route::middleware(['scope:User'])->get('/getSensorsType', [UserSensorController::class, 'getSensorsType']);

    //Script
    Route::middleware(['scope:User'])->post('/createScript', [ScriptController::class, 'store']);
    Route::middleware(['scope:User'])->put('/activateScript', [ScriptController::class, 'activateScript']);
    Route::middleware(['scope:User'])->put('/updateScript/{script_id}', [ScriptController::class, 'update']);
    Route::middleware(['scope:User'])->delete('/deleteScript/{script_id}', [ScriptController::class, 'destroy']);
    Route::middleware(['scope:User'])->get('/getScriptSetting/{script_id}', [ScriptController::class, 'getScriptSetting']);
    Route::middleware(['scope:User'])->put('/executeScript/{user_id}', [ScriptController::class, 'executeScript']);

    //Smart Devices
    Route::middleware(['scope:Admin'])->get('/getAllSmartDevices', [SmartDeviceController::class, 'index']);
    Route::middleware(['scope:Admin'])->post('/createSmartDevice', [SmartDeviceController::class, 'store']);
    Route::middleware(['scope:Admin'])->delete('/deleteSmartDevice/{smt_dev_id}', [SmartDeviceController::class, 'destroy']);
    Route::middleware(['scope:User'])->put('/activateSmartDevice', [SmartDeviceController::class, 'activateSmartDevice']);
    Route::middleware(['scope:User'])->put('/updateSmartDeviceSensorName', [SmartDeviceController::class, 'updateSmartDeviceSensorName']);
    Route::middleware(['scope:Admin'])->get('/exportDevices', [SmartDeviceController::class, 'exportDevices']);
    Route::middleware(['scope:Admin'])->post('/importDevices', [SmartDeviceController::class, 'importDevices']);
});