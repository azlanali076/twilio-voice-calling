<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/token',function(){
    $accessToken = new \Twilio\Jwt\AccessToken(env('TWILIO_ACCOUNT_SID'),env('TWILIO_API_KEY'),env('TWILIO_API_SECRET'),3600,env('TWILIO_IDENTITY'));
    $grant = new \Twilio\Jwt\Grants\VoiceGrant();
    $grant->setOutgoingApplicationSid(env('TWILML_APP_SID'));
    $grant->setIncomingAllow(true);
    $accessToken->addGrant($grant);
    $token = $accessToken->toJWT();
    return response()->json(['token' => $token,'identity' => env('TWILIO_IDENTITY')]);
})->name('api.token');

Route::post('incoming-call',function(Request $request){
    $phone = $request->To;
    $response = new \Twilio\TwiML\VoiceResponse();
    if ($phone == env('TWILIO_NUMBER')) {
        # Receiving an incoming call to the browser from an external phone
        $response = new \Twilio\TwiML\VoiceResponse();
        $dial = $response->dial('');
        $dial->client(env('TWILIO_IDENTITY'));
    } else if (!empty($phone) && strlen($phone) > 0) {
        $number = htmlspecialchars($phone);
        $dial = $response->dial('', ['callerId' => env('TWILIO_NUMBER')]);

        // wrap the phone number or client name in the appropriate TwiML verb
        // by checking if the number given has only digits and format symbols
        if (preg_match("/^[\d\+\-\(\) ]+$/", $number)) {
            $dial->number($number);
        } else {
            $dial->client($number);
        }
    } else {
        $response->say("Thanks for calling!");
    }
    return (string)$response;
});
