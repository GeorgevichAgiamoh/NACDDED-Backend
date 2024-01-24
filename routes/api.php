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
Route::post('login', [ApiController::class,'login']);
Route::post('setFirstAdminUserInfo', [ApiController::class,'setFirstAdminUserInfo']); // For first Admin (call on postman)
Route::post('paystackConf', [ApiController::class,'paystackConf']);
// - PROTECTED

Route::group([
    'middleware'=> ['auth:api'],
], function(){
    Route::post('setDioceseBasicInfo', [ApiController::class,'setDioceseBasicInfo']);
    Route::post('setDioceseGeneralInfo', [ApiController::class,'setDioceseGeneralInfo']);
    Route::post('setSecretaryInfo', [ApiController::class,'setSecretaryInfo']);
    Route::post('authAsAdmin', [ApiController::class,'authAsAdmin']);
    Route::post('uploadFile', [ApiController::class,'uploadFile']);

    Route::post('setAdminUserInfo', [ApiController::class,'setAdminUserInfo']);
    Route::post('setAnnouncements', [ApiController::class,'setAnnouncements']);
    Route::post('setAdmin', [ApiController::class,'setAdmin']);
    Route::post('sendMail', [ApiController::class,'sendMail']);
    Route::post('setEvents', [ApiController::class,'setEvents']);
    Route::post('uploadPayment', [ApiController::class,'uploadPayment']);
    
    Route::get('getDioceseBasicInfo', [ApiController::class, 'getDioceseBasicInfo']);
    Route::get('getDioceseGeneralInfo', [ApiController::class, 'getDioceseGeneralInfo']);
    Route::get('getSecretaryInfo', [ApiController::class, 'getSecretaryInfo']);
    Route::get('getFile/{folder}/{filename}', [ApiController::class, 'getFile']);
    Route::get('fileExists/{folder}/{filename}', [ApiController::class, 'fileExists']);
    Route::get('getAnnouncements', [ApiController::class, 'getAnnouncements']);
    Route::get('getEvents', [ApiController::class, 'getEvents']);
    Route::get('getDiocesePayments', [ApiController::class, 'getDiocesePayments']);

    Route::get('getHighlights', [ApiController::class, 'getHighlights']);
    Route::get('getAdmins', [ApiController::class, 'getAdmins']);
    Route::get('getAdmin/{adminId}', [ApiController::class, 'getAdmin']);
    Route::get('removeAdmin/{adminId}', [ApiController::class, 'removeAdmin']);
    Route::get('getPayments/{payId}', [ApiController::class, 'getPayments']);




    //TODO Remove ---OLD 
    Route::post('setMemberFinancialInfo', [ApiController::class,'setMemberFinancialInfo']);


    
    Route::post('setAdsiInfo', [ApiController::class,'setAdsiInfo']);


    Route::get('getMemberFinancialInfo/{uid}', [ApiController::class, 'getMemberFinancialInfo']);
    Route::get('getMemDuesByYear/{memid}/{year}', [ApiController::class, 'getMemDuesByYear']);

    Route::get('getVerificationStats', [ApiController::class, 'getVerificationStats']);
    Route::get('getMembersByV/{vstat}', [ApiController::class, 'getMembersByV']);
    Route::get('getAsdiInfo', [ApiController::class, 'getAsdiInfo']);

    
    Route::get('refresh', [ApiController::class,'refreshToken']);
    Route::get('logout', [ApiController::class,'logout']);
    Route::get('checkTokenValidity', [ApiController::class,'checkTokenValidity']);
    
});
