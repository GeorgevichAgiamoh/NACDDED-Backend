<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes PREFIX = api

// -- OPEN

Route::post('register', [ApiController::class,'register']);
Route::post('registerAdmin', [ApiController::class,'registerAdmin']);
Route::post('login', [ApiController::class,'login']);
Route::post('adminlogin', [ApiController::class,'adminlogin']);
Route::post('setFirstAdminUserInfo', [ApiController::class,'setFirstAdminUserInfo']); // For first Admin (call on postman)
Route::post('paystackConf', [ApiController::class,'paystackConf']);
Route::post('sendPasswordResetEmail', [ApiController::class,'sendPasswordResetEmail']);
Route::post('resetPassword', [ApiController::class,'resetPassword']);


// - PROTECTED

Route::group([
    'middleware'=> ['auth:api'],
], function(){

    Route::post('setDioceseBasicInfo', [ApiController::class,'setDioceseBasicInfo']);
    Route::post('setDioceseGeneralInfo', [ApiController::class,'setDioceseGeneralInfo']);
    Route::post('setSecretaryInfo', [ApiController::class,'setSecretaryInfo']);
    Route::post('authAsAdmin', [ApiController::class,'authAsAdmin']);
    Route::post('uploadFile', [ApiController::class,'uploadFile']);
    Route::post('setMySchool', [ApiController::class,'setMySchool']);

    
    Route::post('setAnnouncements', [ApiController::class,'setAnnouncements']);
    Route::post('setAdmin', [ApiController::class,'setAdmin']);
    Route::post('sendMail', [ApiController::class,'sendMail']);
    Route::post('setEvent', [ApiController::class,'setEvent']);
    Route::post('uploadPayment', [ApiController::class,'uploadPayment']);
    Route::post('setNacddedInfo', [ApiController::class,'setNacddedInfo']);
    
    
    Route::get('getDioceseBasicInfo/{dioceseId}', [ApiController::class, 'getDioceseBasicInfo']);
    Route::get('getDioceseGeneralInfo/{dioceseId}', [ApiController::class, 'getDioceseGeneralInfo']);
    Route::get('getSecretaryInfo', [ApiController::class, 'getSecretaryInfo']);
    Route::get('getDioceseSecretaries/{dioceseId}', [ApiController::class, 'getDioceseSecretaries']);
    Route::get('getFile/{folder}/{filename}', [ApiController::class, 'getFile']);
    Route::get('fileExists/{folder}/{filename}', [ApiController::class, 'fileExists']);
    Route::get('getAnnouncements', [ApiController::class, 'getAnnouncements']);
    Route::get('getEvents', [ApiController::class, 'getEvents']);
    Route::get('getEvent/{eventId}', [ApiController::class, 'getEvent']);
    Route::get('getDiocesePayments/{dioceseId}/{payId}', [ApiController::class, 'getDiocesePayments']);
    Route::get('getNacddedInfo', [ApiController::class, 'getNacddedInfo']);
    Route::get('getMySchools/{dioceseId}', [ApiController::class, 'getMySchools']);
    Route::get('getFiles/{uid}', [ApiController::class, 'getFiles']);
    Route::get('getMemDuesByYear/{dioceseId}/{year}', [ApiController::class, 'getMemDuesByYear']);
    Route::get('getDioceseEventRegs/{dioceseId}', [ApiController::class, 'getDioceseEventRegs']);
    Route::get('getFreeEvent/{dioceseId}/{eventID}', [ApiController::class, 'getFreeEvent']);
    Route::get('hasDioceseRegisteredEvent/{dioceseId}/{eventID}', [ApiController::class, 'hasDioceseRegisteredEvent']);
    Route::get('manualRegEvent/{dioceseId}/{eventID}', [ApiController::class, 'manualRegEvent']);

    

    Route::get('getHighlights', [ApiController::class, 'getHighlights']);
    Route::get('getAdmins', [ApiController::class, 'getAdmins']);
    Route::get('getAdmin', [ApiController::class, 'getAdmin']);
    Route::get('removeAdmin/', [ApiController::class, 'removeAdmin']);
    Route::get('getPayments/{payId}', [ApiController::class, 'getPayments']);
    Route::get('getSchools', [ApiController::class, 'getSchools']);
    Route::get('getEventRegs/{eventID}', [ApiController::class, 'getEventRegs']);

    
    Route::get('refresh', [ApiController::class,'refreshToken']);
    Route::get('logout', [ApiController::class,'logout']);
    Route::get('checkTokenValidity', [ApiController::class,'checkTokenValidity']);
    
});
