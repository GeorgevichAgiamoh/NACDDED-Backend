<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SSSMails;
use App\Models\admin_user;
use App\Models\adsi_info;
use App\Models\announcements;
use App\Models\diocese_basic_data;
use App\Models\diocese_general_data;
use App\Models\event_regs;
use App\Models\events;
use App\Models\files;
use App\Models\nacdded_info;
use App\Models\password_reset_tokens;
use App\Models\payment_refs;
use App\Models\pays0;
use App\Models\pays9;
use App\Models\pays1;
use App\Models\schools;
use App\Models\secretary_data;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *    title="NACDDED API | Stable Shield Solutions",
 *    version="1.0.0",
 *    description="Backend for the NACDDED project. Powered by Stable Shield Solutions",
 *    @OA\Contact(
 *        email="support@stableshield.com",
 *        name="API Support"
 *    ),
 *    @OA\License(
 *        name="Stable Shield API License",
 *        url="http://stableshield.com/api-licenses"
 *    )
 * )
 */


class ApiController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/registerAdmin",
     *     tags={"Unprotected"},
     *     summary="Create the admin, DO NOT CALL, YOU DONT NEED THIS ENDPOINT !!!!",
     *     @OA\Response(response="200", description="Login Successfully"),
     * )
     */
    public function registerAdmin(){
        User::create([
            "email"=> "admin@nacdded.org.ng",
            "password"=> bcrypt("123456"),
        ]);
        admin_user::create([
            "email"=> 'admin@nacdded.org.ng',
            "lname"=> 'NACDDED',
            "oname"=> 'ADMIN USER',
            "role"=> '0',
            "pd1"=> '1',
            "pd2"=> '1',
            "pw1"=> '1',
            "pw2"=> '1',
            "pp1"=> '1',
            "pp2"=> '1',
            "pm1"=> '1',
            "pm2"=> '1',
            
        ]);
        $token = JWTAuth::attempt([
            "email"=> "admin@nacdded.org.ng",
            "password"=> "123456",
        ]);
        if(!empty($token)){
            return response()->json([
                "status"=> true,
                "message"=> "User created successfully",
                "token"=> $token
            ]);
        }
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "User created successfully",
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Unprotected"},
     *     summary="YOU DONT NEED THIS ENDPOINT !!!!",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="fname", type="string"),
     *             @OA\Property(property="lname", type="string"),
     *             @OA\Property(property="phn", type="string"),
     *             @OA\Property(property="addr", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Login Successfully"),
     * )
     */
    public function register(Request $request){
        //Data validation
        $request->validate([
            "email"=>"required|email|unique:users",
            "password"=> "required",
            "name"=> "required",
            "fname"=> "required",
            "lname"=> "required",
            "phn"=> "required",
            "addr"=> "required",
        ]);
        //Save Data to DB
        $dusr = User::create([
            "email"=> $request->email,
            "password"=> bcrypt($request->password),
        ]);
        diocese_basic_data::create([
            "diocese_id"=> strval($dusr->id),
            "name"=> $request->name,
            "phn"=> $request->phn,
            "verif"=> "0",
        ]);//Create Diocese
        secretary_data::create([
            "email"=> $request->email,
            "fname"=> $request->fname,
            "lname"=> $request->lname,
            "mname"=> "",
            "sex"=> "",
            "phn"=> $request->phn,
            "addr"=> $request->addr,
            "diocese_id"=> strval($dusr->id),
        ]);
        $token = JWTAuth::attempt([
            "email"=> $request->email,
            "password"=> $request->password,
        ]);
        if(!empty($token)){
            return response()->json([
                "status"=> true,
                "message"=> "User created successfully: ".strval($dusr->id),
                "token"=> $token
            ]);
        }
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "User created successfully",
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/sendPasswordResetEmail",
     *     tags={"Unprotected"},
     *     summary="Send reset email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Password reset token sent to mail"),
     * )
     */
    public function sendPasswordResetEmail(Request $request){
        //Data validation
        $request->validate([
            "email"=>"required",
        ]);
        $eml = $request->email;
        $pld = User::where("email","=", $eml)->first();
        if($pld){
            $token = Str::random(60); //Random reset token
            password_reset_tokens::updateOrCreate(
                ['email' => $eml],
                ['email' => $eml, 'token' => $token]
            );
            $data = [
                'name' => 'NACDDED USER',
                'subject' => 'Reset your NACDDED password',
                'body' => 'Please go to this link to reset your password. It will expire in 1 hour:',
                'link'=>'https://portal.nacdded.org.ng/passwordreset/'.$token,
            ];
        
            Mail::to($eml)->send(new SSSMails($data));
            
            return response()->json([
                "status"=> true,
                "message"=> "Password reset token sent to mail",
            ]);   
        }
        // Respond
        return response()->json([
            "status"=> false,
            "message"=> "Email not found",
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/resetPassword",
     *     tags={"Unprotected"},
     *     summary="change user password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="pwd", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Password reset token sent to mail"),
     * )
     */
    public function resetPassword(Request $request){
        //Data validation
        $request->validate([
            "token"=>"required",
            "pwd"=>"required",
        ]);
        $pld = password_reset_tokens::where("token","=", $request->token)->first();
        if($pld){
            $email = $pld->email;
            $usr = User::where("email","=", $email)->first();
            if($usr){
                $usr->update([
                    "password"=>bcrypt($request->pwd),
                ]);
                return response()->json([
                    "status"=> true,
                    "message"=> "Success. Please login again"
                ]);
            }
            return response()->json([
                "status"=> false,
                "message"=> "User not found",
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Denied. Invalid/Expired Token",
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/adminlogin",
     *     tags={"Unprotected"},
     *     summary="Login as admin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Login Successfully"),
     * )
     */
    public function adminlogin(Request $request){
        //Data validation
        $request->validate([
            "email"=>"required|email",
            "password"=> "required",
        ]);
        $user = User::where("email","=", $request->email)->first();
        $apld = admin_user::where("email","=", $user->email)->first();
        if($apld){
            $customClaims = [
                'role'=>$apld->role,
                'pd1' => $apld->pd1, 
                'pd2' => $apld->pd2, 
                'pw1' => $apld->pw1, 
                'pw2' => $apld->pw2, 
                'pp1' => $apld->pp1, 
                'pp2' => $apld->pp2, 
                'pm1' => $apld->pm1, 
                'pm2' => $apld->pm2, 
            ];
            $token = JWTAuth::customClaims($customClaims)->fromUser($user);
            return response()->json([
                "status"=> true,
                "message"=> "Admin authorization granted",
                "token"=> $token,
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Invalid login details",
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Unprotected"},
     *     summary="Login to the application",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Login Successfully"),
     * )
     */
    public function login(Request $request){
        //Data validation
        $request->validate([
            "email"=>"required|email",
            "password"=> "required",
        ]);
        $token = JWTAuth::attempt([
            "email"=> $request->email,
            "password"=> $request->password,
        ]);
        if(!empty($token)){
            return response()->json([
                "status"=> true,
                "message"=> "User login successfully",
                "token"=> $token,
            ]);
        }
        // Respond
        return response()->json([
            "status"=> false,
            "message"=> "Invalid login details",
        ]);
    }


    //Paystack Webhook (POST, formdata)
    public function paystackConf(Request $request){ 
        $payload = json_decode($request->input('payload'), true);
        if($payload['event'] == "charge.success"){
            $ref = $payload['data']['reference'];
            $pld = payment_refs::where("ref","=", $ref)->first();
            if(Str::startsWith($ref,"nacdded-")){ //Its for NAC
                if(!$pld){ // Its unique
                    $payinfo = explode('-',$ref);
                    $amt = $payinfo[2];
                    $nm = $payload['data']['metadata']['name'];
                    $tm = $payload['data']['metadata']['time'];
                    payment_refs::create([
                        "ref"=> $ref,
                        "amt"=> $amt,
                        "time"=> $tm,
                    ]);
                    $upl = [
                        "diocese_id"=>$payinfo[3],
                        "ref"=> $ref,
                        "name"=> $nm,
                        "time"=> $tm,
                        "amt"=> intval($amt),
                    ];
                    if($payinfo[1]=='0'){
                        $yr = $payload['data']['metadata']['year'];
                        $upl['year'] = $yr;
                        pays0::create($upl);
                    }else{ // ie 1
                        $ev = $payload['data']['metadata']['event']; //Event ID
                        $upl['event'] = $ev;
                        pays1::create($upl);
                        //Create event_regs
                        event_regs::create([
                            'event_id' => $ev,
                            'diocese_id'=> $payinfo[3],
                            'proof'=> $ref,
                            'verif'=>'0'
                        ]);
                    }
                    Log::info('SUCCESS');
                }else{
                    Log::info('PLD EXISTS'.json_encode($pld));
                }
            }else{
                Log::info('STR BAD '.$ref);
            }
        }else{
            Log::info('EVENTS BAD '.$payload['event']);
        }
        return response()->json(['status' => 'success'], 200);
    }

    //---Protected from here


    /**
     * @OA\Get(
     *     path="/api/getAnnouncements",
     *     tags={"Api"},
     *     summary="Get Announcements",
     *     description="Use this endpoint to get information about announcements.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getAnnouncements(){
        $pld = announcements::all();
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/uploadFile",
     *     tags={"Api"},
     *     summary="Upload a file",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="file", type="file"),
     *             @OA\Property(property="filename", type="string"),
     *             @OA\Property(property="folder", type="string"),
     *             @OA\Property(property="diocese_id", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function uploadFile(Request $request){
        $request->validate([
            'file' => 'required', //|mimes:jpeg,png,jpg,gif,svg|max:2048
            'filename' => 'required',
            'folder' => 'required',
            'diocese_id'=> 'required',
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $request->filename;
            $folder = $request->folder;
            if (!Storage::disk('public')->exists($folder)) {
                // If it doesn't exist, create the directory
                Storage::disk('public')->makeDirectory($folder);
            }
            Storage::disk('public')->put($folder.'/'. $filename, file_get_contents($file));
            // Log It
            files::create([
                'diocese_id' => $request->diocese_id,
                'file'=> $filename,
                'folder'=> $folder,
            ]);
            return response()->json([
                "status"=> true,
                "message"=> "Success"
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "message"=> "No file provided"
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/getFiles/{diocese_id}",
     *     tags={"Api"},
     *     summary="Get all Files belonging to a diocese ",
     *     description="API: Use this endpoint to get all files by a diocese",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="diocese_id",
     *         in="path",
     *         required=true,
     *         description="Diocese ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *      ),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getFiles($uid){
        $pld = files::where('diocese_id', $uid)->get();
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/getFile/{folder}/{filename}",
     *     tags={"Api"},
     *     summary="Get File",
     *     description="API: Use this endpoint to get a file by providing the folder and filename as path parameters.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="folder",
     *         in="path",
     *         required=true,
     *         description="Name of the folder",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="Name of the file",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/octet-stream",
     *              @OA\Schema(type="file")
     *          )
     *      ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="File not found"),
     * )
     */
    public function getFile($folder,$filename){
        if (Storage::disk('public')->exists($folder.'/'.$filename)) {
            return response()->file(Storage::disk('public')->path($folder.'/'.$filename));
        } else {
            return response()->json([
                "status" => false,
                "message" => "File not found",
            ], 404);
        }
    }

    

    /**
     * @OA\Get(
     *     path="/api/fileExists/{folder}/{filename}",
     *     tags={"Api"},
     *     summary="Check if File Exists",
     *     description="API: Use this endpoint to check if a file exists by providing the folder and filename as path parameters.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="folder",
     *         in="path",
     *         required=true,
     *         description="Name of the folder",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="Name of the file",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function fileExists($folder,$filename){
        if (Storage::disk('public')->exists($folder.'/'.$filename)) {
            return response()->json([
                "status" => true,
                "message" => "Yes, it does",
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "File not found",
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/getEvents",
     *     tags={"Api"},
     *     summary="Get Events",
     *     description="Use this endpoint to get information about events.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve. If not specified, all will be returned",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         required=true,
     *         description="Get events after mills ",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         required=true,
     *         description="Get events before mills ",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getEvents(){
        $start = 0;
        $count = 5;
        if(request()->has('start') && request()->has('count')) {
            $start = request()->input('start');
            $count = request()->input('count');
        }
        if(request()->has('from') && request()->has('to')) {
            $from = request()->input('from');
            $to = request()->input('to');
            $pld = events::where('time', '>', $from)->where('time', '<', $to)->skip($start)->take($count)->get();
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> $pld,
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "from & to QP required",
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/deleteEvent/eventId",
     *     tags={"Admin"},
     *     summary="Remove an event",
     *     description="ADMIN: !!! Wont delete registration .",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Email of the admin to be removed",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Admin removed successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function deleteEvent($eventId){
        $pw2 = auth()->payload()->get('pw2');
        if ( $pw2!=null && $pw2=='1') {
            events::where('id', $eventId)->delete();
            event_regs::where('event_id', $eventId)->delete();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }


    /**
     * @OA\Get(
     *     path="/api/getEventStat",
     *     tags={"Admin"},
     *     summary="Get Highlights",
     *     description="ADMIN: Use this endpoint to get information about highlights.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getEventStat(){
        $pw1 = auth()->payload()->get('pw1');
        if ( $pw1!=null  && $pw1=='1') {
            $totalEvents = events::count();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> [
                    'totalEvents'=>$totalEvents
                ],
            ]);  
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/getEvent/{eventId}",
     *     tags={"Api"},
     *     summary="Get an Event",
     *     description="Use this endpoint to get information about an event.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getEvent($eventId){
        $pld = events::where('id', $eventId)->first();
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/getFreeEvent/{dioceseId}/{eventId}",
     *     tags={"Api"},
     *     summary="Register for a free event",
     *     description="Use this endpoint to reg for a free event.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="Diocese Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getFreeEvent($dioceseId,$eventId){
        $pld = events::where('id', $eventId)->first();
        if($pld){
            if($pld->fee == '0'){
                event_regs::create([
                    'event_id' => $eventId,
                    'diocese_id'=> $dioceseId,
                    'proof'=> '', //Since free
                    'verif'=>'0'
                ]);
                return response()->json([
                    "status"=> true,
                    "message"=> "Success",
                ]);
            }
            return response()->json([
                "status"=> false,
                "message"=> "Event is not free",
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Event not found",
        ]);
    }

     /**
     * @OA\Get(
     *     path="/api/manualRegEvent/{dioceseId}/{eventId}",
     *     tags={"Api"},
     *     summary="Manual reg for event (file proof)",
     *     description="Use this endpoint to manually reg for a an event.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="Diocese Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function manualRegEvent($dioceseId,$eventId){
        $pld = events::where('id', $eventId)->first();
        if($pld){
            event_regs::create([
                'event_id' => $eventId,
                'diocese_id'=> $dioceseId,
                'proof'=> 'f', //Since free
                'verif'=>'0'
            ]);
            return response()->json([
                "status"=> true,
                "message"=> "Success",
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Event not found",
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/hasDioceseRegisteredEvent/{dioceseId}/{eventId}",
     *     tags={"Api"},
     *     summary="Check if diocese has registred for an event",
     *     description="Use this endpoint to check if diocese has registred for an event.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="Diocese Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function hasDioceseRegisteredEvent($dioceseId,$eventId){
        $recordExists = event_regs::where('event_id', $eventId)
            ->where('diocese_id', $dioceseId)
            ->exists();
        if($recordExists){
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> ["exists"=>"1"],
            ]);
        }
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> ["exists"=>"0"],
        ]);
    }

     /**
     * @OA\Post(
     *     path="/api/registerOfflinePayment",
     *     tags={"Api"},
     *     summary="Register an offline payment",
     *     description="Register an offline payment to be approved by admin",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ref", type="string", description="Properly formatted payment ref"),
     *             @OA\Property(property="name", type="string", description="Name of the diocese"),
     *             @OA\Property(property="time", type="string", description="Time paid in millis"),
     *             @OA\Property(property="proof", type="string", description="Proof of payment (use `f` for File)"),
     *             @OA\Property(property="meta", type="string", description="Depends on the type of payment - year or event"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function registerOfflinePayment(Request $request){ 
        $request->validate([
            "ref"=> "required",
            "name"=> "required",
            "time"=> "required",
            "proof"=> "required",
            "meta"=> "required",
        ]);
        $ref = $request->ref;
        $payinfo = explode('-',$ref);
        $amt = $payinfo[2];
        $nm = $request->name; 
        $tm = $request->time;
        $typ = $payinfo[1]; 
        $upl = [
            "diocese_id"=>$payinfo[3],
            "type"=>$typ,
            "ref"=> $ref,
            "name"=> $nm,
            "time"=> $tm,
            "proof"=> $request->proof,
            "amt"=> intval($amt),
            "meta"=> $request->meta,
        ];
        pays9::create($upl);
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success"
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/setDioceseBasicInfo",
     *     tags={"Api"},
     *     summary="Set Diocese Basic Info",
     *     description="This sensitive endpoint is used to set basic information about a diocese. You must re-login immediately after calling it.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="diocese_id", type="string", description="get Diocese ID from getSecretaryInfo endpoint"),
     *             @OA\Property(property="name", type="string", description="Name of the diocese"),
     *             @OA\Property(property="phn", type="string", description="Phone number of the diocese"),
     *             @OA\Property(property="verif", type="string", description="Verification status (0 or 1)"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Diocese Basic info set/updated successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setDioceseBasicInfo(Request $request){
        $request->validate([
            "diocese_id"=>"required",
            "name"=> "required",
            "phn"=> "required",
            "verif"=> "required",
        ]);
        $dbd = diocese_basic_data::where("diocese_id", $request->diocese_id)->first();
        if($dbd){
            $dbd->update([
                "name"=> $request->name,
                "phn"=> $request->phn,
                "verif"=> $request->verif,
            ]);
            return response()->json([
                "status"=> true,
                "message"=> "Success. Please login again"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Diocese not found"
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/getDioceseBasicInfo/{dioceseId}",
     *     tags={"Api"},
     *     summary="Get Diocese Basic Info",
     *     description="Use this endpoint to get basic information about a diocese.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="Diocese Id",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getDioceseBasicInfo($dioceseId){
        $pld = diocese_basic_data::where("diocese_id", $dioceseId)->first();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/setDioceseGeneralInfo",
     *     tags={"Api"},
     *     summary="Set Diocese General Info",
     *     description="Use this endpoint to set general information about a diocese.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="diocese_id", type="string", description="Get it from getMyDiocese endpoint"),
     *             @OA\Property(property="state", type="string", description="State of the diocese"),
     *             @OA\Property(property="lga", type="string", description="Local Government Area of the diocese"),
     *             @OA\Property(property="addr", type="string", description="Address of the diocese"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Diocese general info set successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setDioceseGeneralInfo(Request $request){
        $request->validate([
            "diocese_id"=>"required",
            "state"=> "required",
            "lga"=> "required",
            "addr"=> "required",
        ]);
        diocese_general_data::updateOrCreate(
            ["diocese_id"=> $request->diocese_id,],
            [
            "state"=> $request->state,
            "lga"=> $request->lga,
            "addr"=> $request->addr,
        ]);
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success"
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/getDioceseGeneralInfo/{dioceseId}",
     *     tags={"Api"},
     *     summary="Get Diocese General Info",
     *     description="Use this endpoint to get general information about a diocese.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="Diocese Id",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getDioceseGeneralInfo($dioceseId){
        $pld = diocese_general_data::where("diocese_id", $dioceseId)->first();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/setSecretaryInfo",
     *     tags={"Api"},
     *     summary="Set Secretary Info",
     *     description="Use this endpoint to craete/update a secretary.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", description="Email of the secretary"),
     *             @OA\Property(property="fname", type="string", description="First name of the secretary"),
     *             @OA\Property(property="lname", type="string", description="Last name of the secretary"),
     *             @OA\Property(property="mname", type="string", description="Middle name of the secretary"),
     *             @OA\Property(property="sex", type="string", description="Sex of the secretary"),
     *             @OA\Property(property="phn", type="string", description="Phone number of the secretary"),
     *             @OA\Property(property="addr", type="string", description="Address of the secretary"),
     *             @OA\Property(property="diocese_id", type="string", description="Diocese ID"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Secretary info set successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setSecretaryInfo(Request $request){
        $request->validate([
            "email"=>"required",
            "fname"=> "required",
            "lname"=> "required",
            "mname"=> "required",
            "sex"=> "required",
            "phn"=> "required",
            "addr"=> "required",
            "diocese_id"=>"required",
        ]);
        $pld = User::where("email","=", $request->email)->first();
        if(!$pld){ //Secretary is new
            User::create([
                "email"=> $request->email,
                "password"=> bcrypt("123456"),
            ]);
        }
        secretary_data::updateOrCreate(
            ["email"=> $request->email,],
            [
            "fname"=> $request->fname,
            "lname"=> $request->lname,
            "mname"=> $request->mname,
            "sex"=> $request->sex,
            "phn"=> $request->phn,
            "addr"=> $request->addr,
            "diocese_id"=> $request->diocese_id,
        ]);
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success"
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/getSecretaryInfo",
     *     tags={"Api"},
     *     summary="Get Secretary Info",
     *     description="Use this endpoint to get information about a secretary. Itll include a diocese id ",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Email of the secretary",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getSecretaryInfo(){
        if(request()->has('email')) {
            $eml = request()->input('email');
            $pld = secretary_data::where("email", $eml)->first();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> $pld,
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Email required",
        ]);
    }



    /**
     * @OA\Get(
     *     path="/api/getDioceseSecretaries/{dioceseId}",
     *     tags={"Api"},
     *     summary="Get All Secretaries for this diocese",
     *     description="Use this endpoint to get all secretaries in this diocese",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="Diocese ID get from getSecretaryInfo endpoint",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getDioceseSecretaries($dioceseId){
        $pld = secretary_data::where("diocese_id", $dioceseId)->get();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }



    /**
     * @OA\Get(
     *     path="/api/getDiocesePayments",
     *     tags={"Api"},
     *     summary="Get Diocese Payments",
     *     description="Use this endpoint to get all payments by a diocese.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="ID of the diocese",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="payId",
     *         in="path",
     *         required=true,
     *         description="ID of the payment record to be retrieved",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=false,
     *         description="Start index for limiting the result",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getDiocesePayments($dioceseId,$payId){
        $start = 0;
        $count = 20;
        if(request()->has('start') && request()->has('count')) {
            $start = request()->input('start');
            $count = request()->input('count');
        }
        $pld = null;
        if($payId == '0'){
            $pld = pays0::where('diocese_id', $dioceseId)->skip($start)->take($count)->get();
        }else{
            $pld = pays1::where('diocese_id', $dioceseId)->skip($start)->take($count)->get();
        }
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);  
    }

    /**
     * @OA\Get(
     *     path="/api/getEventRegs/{eventID}",
     *     tags={"Api"},
     *     summary="Get list of registration for an event",
     *     description="Use this endpoint to get list of registration for an event.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="eventID",
     *         in="path",
     *         required=true,
     *         description="ID of the event (ie the time column)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=false,
     *         description="Start index for limiting the result",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getEventRegs($eventID){
        $pw1 = auth()->payload()->get('pw1');
        if ( $pw1!=null  && $pw1=='1') { //Can read from dir
            $start = 0;
            $count = 20;
            if(request()->has('start') && request()->has('count')) {
                $start = request()->input('start');
                $count = request()->input('count');
            }
            $pld = event_regs::where('event_id', $eventID)->skip($start)->take($count)->get();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> $pld,
            ]); 
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/getDioceseEvents",
     *     tags={"Api"},
     *     summary="Get Diocese Registered Events",
     *     description="Use this endpoint to get all payments by a diocese.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="ID of the diocese",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=false,
     *         description="Start index for limiting the result",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getDioceseEventRegs($dioceseId){
        $start = 0;
        $count = 20;
        if(request()->has('start') && request()->has('count')) {
            $start = request()->input('start');
            $count = request()->input('count');
        }
        $pld = event_regs::where('diocese_id', $dioceseId)->skip($start)->take($count)->get();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);  
    }

    //GET
    public function getMemDuesByYear($dioceseId, $year){
        $dues = pays0::where('diocese_id', $dioceseId)->where('year', $year)->first();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $dues,
        ]);  
    }






    //--------------- ADMIN CODES


     /**
     * @OA\Post(
     *     path="/api/approveOfflinePayment",
     *     tags={"Admin"},
     *     summary="Approve offline payment",
     *     description="ADMIN: To approve an offline payment (the payment will auto-resolve to its `type` - ie dues/event)",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", description="Payment ID"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function approveOfflinePayment(Request $request){ 
        if ( $this->permOk('pp2')) {
            $request->validate([
                "id"=> "required",
            ]);
            $pld = pays9::where('id', $request->id)->first();
            $ref = $pld->ref;
            $payinfo = explode('-',$ref);
            $amt = $payinfo[2];
            $nm = $pld->name; 
            $tm = $pld->time;
            $upl = [
                "diocese_id"=>$payinfo[3],
                "ref"=> $ref,
                "name"=> $nm,
                "time"=> $tm,
                "amt"=> intval($amt)
            ];
            if ($payinfo[1]=='0'){
                $yr = $pld->meta;
                $upl['year'] = $yr;
                pays0::create($upl);
            }else{ // ie 1
                $ev = $pld->meta;
                $upl['event'] = $ev;
                pays1::create($upl);
                //TODO Log Event_Reg
                event_regs::create([
                    'event_id' => $ev,
                    'diocese_id'=> $payinfo[3],
                    'proof'=> $ref,
                    'verif'=>'1'
                ]);

            }
            Pays9::where('id', $request->id)->delete();
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Success"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Post(
     *     path="/api/deleteOfflinePayment",
     *     tags={"Admin"},
     *     summary="Delete/Deny offline payment",
     *     description="ADMIN: To delete/deny an offline payment ",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", description="Payment ID"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function deleteOfflinePayment(Request $request){ 
        if ( $this->permOk('pp2')) {
            $request->validate([
                "id"=> "required",
            ]);
            $pld = pays9::where('id', $request->id)->first();
            Pays9::where('id', $request->id)->delete();
            if (Storage::disk('public')->exists('pends' . '/' . $pld->diocese_id.'_'.$pld->time)) {
                Storage::disk('public')->delete('pends' . '/' . $pld->diocese_id.'_'.$pld->time);
            }
            // Delete Log
            files::where('folder', 'pends')->where('file', $pld->diocese_id.'_'.$pld->time)->delete();
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Success"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }


    /**
     * @OA\Get(
     *     path="/api/getHighlights",
     *     tags={"Admin"},
     *     summary="Get Highlights",
     *     description="ADMIN: Use this endpoint to get information about highlights.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getHighlights(){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $totalSchools = schools::count();
            $totalDiocese = diocese_basic_data::count();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> [
                    'totalSchools'=>$totalSchools,
                    'totalDiocese'=>$totalDiocese
                ],
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Post(
     *     path="/api/setAnnouncements",
     *     tags={"Admin"},
     *     summary="Create Announcement",
     *     description="ADMIN: Use this endpoint to create an announcement.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", description="Title of the announcement"),
     *             @OA\Property(property="msg", type="string", description="Message content of the announcement"),
     *             @OA\Property(property="time", type="string", description="Time of the announcement"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Announcement created successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setAnnouncements(Request $request){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
              $request->validate([
                "title"=>"required",
                "msg"=> "required",
                "time"=> "required",
            ]);
            announcements::create([
                "title"=> $request->title,
                "msg"=> $request->msg,
                "time"=> $request->time,
            ]);
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Announcement Added"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Post(
     *     path="/api/deleteAnnouncements",
     *     tags={"Admin"},
     *     summary="Delete Announcement",
     *     description="ADMIN: Use this endpoint to delete an announcement.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", description="ID of the announcement"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function deleteAnnouncements(Request $request){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $request->validate([
                "id"=>"required",
            ]);
            announcements::where('id', $request->id)->delete();
            return response()->json([
                "status"=> true,
                "message"=> "Success"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

     /**
     * @OA\Get(
     *     path="/api/getAdmins",
     *     tags={"Admin"},
     *     summary="Get all admins",
     *     description="ADMIN: Use this endpoint to get information about all admins.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
     public function getAdmins(){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $pld = admin_user::all();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> $pld
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/getAdmin",
     *     tags={"Admin"},
     *     summary="Get Admin",
     *     description="ADMIN: Use this endpoint to get information about a specific admin by providing the email as a query parameter.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Email of the admin to be retrieved",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getAdmin(){
        $role = auth()->payload()->get('role');
        if ( $role!=null) { //Granted to all admin as is needed on first page
            if(request()->has('email')) {
                $eml = request()->input('email');
                $pld = admin_user::where('email', $eml)->first();
                return response()->json([
                    "status"=> true,
                    "message"=> "Success",
                    "pld"=> $pld
                ]);   
            }
            return response()->json([
                "status"=> false,
                "message"=> "Email required",
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Post(
     *     path="/api/setAdmin",
     *     tags={"Admin"},
     *     summary="Create a new admin",
     *     description="ADMIN: This endpoint is used to create a new admin. Permissions for various actions are specified using pd1, pd2, pw1, pw2, pp1, pp2, pm1, and pm2 properties.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="lname", type="string"),
     *             @OA\Property(property="oname", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="pd1", type="string"),
     *             @OA\Property(property="pd2", type="string"),
     *             @OA\Property(property="pw1", type="string"),
     *             @OA\Property(property="pw2", type="string"),
     *             @OA\Property(property="pp1", type="string"),
     *             @OA\Property(property="pp2", type="string"),
     *             @OA\Property(property="pm1", type="string"),
     *             @OA\Property(property="pm2", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Admin created successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setAdmin(Request $request){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $request->validate([
                "memid"=>"required",
                "lname"=>"required",
                "oname"=> "required",
                "eml"=> "required",
                "role"=>"required",
                "pd1"=> "required",
                "pd2"=> "required",
                "pw1"=> "required",
                "pw2"=> "required",
                "pp1"=>"required",
                "pp2"=> "required",
                "pm1"=> "required",
                "pm2"=>"required",
            ]);
            admin_user::updateOrCreate(
                ["memid"=> $request->memid,],
                [
                "lname"=> $request->lname,
                "oname"=> $request->oname,
                "eml"=> $request->eml,
                "role"=> $request->role,
                "pd1"=> $request->pd1,
                "pd2"=> $request->pd2,
                "pw1"=> $request->pw1,
                "pw2"=> $request->pw2,
                "pp1"=> $request->pp1,
                "pp2"=> $request->pp2,
                "pm1"=> $request->pm1,
                "pm2"=> $request->pm2,
            ]);
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Admin Added"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    
    /**
     * @OA\Get(
     *     path="/api/removeAdmin",
     *     tags={"Admin"},
     *     summary="Remove an admin",
     *     description="ADMIN: Use this endpoint to remove an admin by providing the email as a query parameter.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Email of the admin to be removed",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Admin removed successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function removeAdmin(){
        $role = auth()->payload()->get('role');
        if ( $role!=null && $role=='0') {
            if(request()->has('email')) {
                $eml = request()->input('email');
                $dels = admin_user::where('email', $eml)->delete();
                if($dels>0){
                    return response()->json([
                        "status"=> true,
                        "message"=> "Success",
                    ]);  
                }
                return response()->json([
                    "status"=> false,
                    "message"=> "Nothing to delete"
                ]);    
            }
            return response()->json([
                "status"=> false,
                "message"=> "Email required",
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Post(
     *     path="/api/sendMail",
     *     tags={"Admin"},
     *     summary="Send Email",
     *     description="ADMIN: Use this endpoint to send an email.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="to", type="string", description="Recipient email address"),
     *             @OA\Property(property="subject", type="string", description="Email subject"),
     *             @OA\Property(property="message", type="string", description="Email body/message"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Email sent successfully", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function sendMail(Request $request){
        $pd2 = auth()->payload()->get('pd2');
        if ( $pd2!=null  && $pd2=='1') { //Can write to dir
            $request->validate([
                "name"=>"required",
                "email"=>"required",
                "subject"=>"required",
                "body"=> "required",
            ]);
            $data = [
                'name' => $request->name,
                'subject' => $request->subject,
                'body' => $request->body,
            ];
        
            Mail::to($request->email)->send(new SSSMails($data));
            
            return response()->json([
                "status"=> true,
                "message"=> "Mailed Successfully",
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Post(
     *     path="/api/setEvent",
     *     tags={"Admin"},
     *     summary="Create Event",
     *     description="ADMIN: Use this endpoint to create an event.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", description="Title of the event"),
     *             @OA\Property(property="time", type="integer", description="Time of the event"),
     *             @OA\Property(property="venue", type="string", description="Venue of the event"),
     *             @OA\Property(property="fee", type="string", description="Fee for the event"),
     *             @OA\Property(property="start", type="integer", description="Start time of the event"),
     *             @OA\Property(property="end", type="integer", description="End time of the event"),
     *             @OA\Property(property="theme", type="string", description="Theme of the event"),
     *             @OA\Property(property="speakers", type="string", description="Speakers in the event. I recommend comma separated"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="event id is returned as payload "),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setEvent(Request $request){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
              $request->validate([
                "title"=>"required",
                "time"=> "required",
                "venue"=> "required",
                "fee"=> "required",
                "start"=> "required",
                "end"=> "required",
                "theme"=> "required",
                "speakers"=> "required",
            ]);
            $ev = events::create([
                "title"=> $request->title,
                "time"=> $request->time,
                "venue"=> $request->venue,
                "fee"=> $request->fee,
                "start"=> $request->start,
                "end"=> $request->end,
                "theme"=> $request->theme,
                "speakers"=> $request->speakers,
            ]);
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Event Added",
                "pld"=>['id'=>strval($ev->id)]
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Post(
     *     path="/api/uploadPayment",
     *     tags={"Admin"},
     *     summary="Upload Payment Record",
     *     description="ADMIN: Use this endpoint to manually upload payment records.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="ref", type="string", description="Reference of the payment"),
     *             @OA\Property(property="name", type="string", description="Name associated with the payment"),
     *             @OA\Property(property="time", type="string", description="Time of the payment"),
     *             @OA\Property(property="year", type="string", description="Year for annual dues (optional)"),
     *             @OA\Property(property="event", type="string", description="Event ID for event-related payments (optional)"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Payment record uploaded successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function uploadPayment(Request $request){ 
        $pp2 = auth()->payload()->get('pp2');
        if ( $pp2!=null  && $pp2=='1') {
            $request->validate([
                "ref"=> "required",
                "name"=> "required",
                "time"=> "required",
            ]);
            $ref = $request->ref;
            $payinfo = explode('-',$ref);
            $amt = $payinfo[2];
            $nm = $request->name; 
            $tm = $request->time; 
            /*payment_refs::create([ DONT INCLUDE SINCE CUSTOM RECORDS NOT ON PAYSTACK
                "ref"=> $ref,
                "amt"=> $amt,
                "time"=> $tm,
            ]);*/
            $upl = [
                "diocese_id"=>$payinfo[3],
                "ref"=> $ref,
                "name"=> $nm,
                "time"=> $tm,
                "amt"=> intval($amt),
            ];
            if($payinfo[1]=='0'){
                $yr = $request->year;
                $upl['year'] = $yr;
                pays0::create($upl);
            }else{ //TODO event_regs
                $ev = $request->event;
                $upl['event'] = $ev;
                pays1::create($upl);
                event_regs::create([
                    'event_id' => $ev,
                    'diocese_id'=> $payinfo[3],
                    'proof'=> $ref,
                    'verif'=>'1'
                ]);
            }
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Success"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/getRevenue/{payId}",
     *     tags={"Admin"},
     *     summary="Get Payment Records",
     *     description="ADMIN: Use this endpoint to get total revenue & payment count by providing the payId as a path parameter.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="payId",
     *         in="path",
     *         required=true,
     *         description="ID of the payment record to be retrieved",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getRevenue($payId){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $total = 0;
            $count = 0;
            if($payId=='0'){
                $count = pays0::count();
                $total = pays0::sum('amt');
            }else if($payId=='1'){
                $count = pays1::count();
                $total = pays1::sum('amt');
            }else if($payId=='9'){
                $count = pays9::count();
                $total = pays9::sum('amt');
            }
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> [
                    'total'=>$total,
                    'count'=>$count,
                ],
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

     /**
     * @OA\Get(
     *     path="/api/getPayments/{payId}",
     *     tags={"Admin"},
     *     summary="Get Payment Records",
     *     description="ADMIN: Use this endpoint to get payment records by providing the payId as a path parameter. You can limit the result using start and count query parameters.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="payId",
     *         in="path",
     *         required=true,
     *         description="ID of the payment record to be retrieved",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=false,
     *         description="Start index for limiting the result",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
     public function getPayments($payId){
        $pp1 = auth()->payload()->get('pp1');
        if ( $pp1!=null  && $pp1=='1') { //Can read from dir
            $start = 0;
            $count = 20;
            if(request()->has('start') && request()->has('count')) {
                $start = request()->input('start');
                $count = request()->input('count');
            }
            $pld = null;
            if( $payId=='0' ){
                $pld = pays0::take($count)->skip($start)->get();
            }
            if( $payId=='1' ){
                $pld = pays1::take($count)->skip($start)->get();
            }
            if( $payId=='9' ){
                $pld = pays9::take($count)->skip($start)->get();
            }
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> $pld
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }


    /**
     * @OA\Get(
     *     path="/api/getVerificationStats",
     *     tags={"Admin"},
     *     summary="Get statistics on user verification",
     *     description="ADMIN: Use this endpoint to get statistics on diocese verification",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getVerificationStats(){
        $pd1 = auth()->payload()->get('pd1');
        if ( $pd1!=null  && $pd1=='1') { //Can read from dir
            $totalVerified = diocese_basic_data::where('verif', '1')->count();
            $totalUnverified = diocese_basic_data::where('verif', '0')->count();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> [
                    'totalVerified'=>$totalVerified,
                    'totalUnverified'=>$totalUnverified,
                ],
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/getDioceseByV/{vstat}",
     *     tags={"Admin"},
     *     summary="Get diocese by whether they are verified or not",
     *     description="ADMIN: Use this endpoint to get diocese by whether they are verified or not by providing the vstat as a path parameter. You can limit the result using start and count query parameters.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="vstat",
     *         in="path",
     *         required=true,
     *         description="0 = Unverified / 1 = Verified",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=false,
     *         description="Start index for limiting the result",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getDioceseByV($vstat){
        $start = 0;
        $count = 20;
        if(request()->has('start') && request()->has('count')) {
            $start = request()->input('start');
            $count = request()->input('count');
        }
        $pd1 = auth()->payload()->get('pd1');
        if ( $pd1!=null  && $pd1=='1') { //Can read from dir
            $members = diocese_basic_data::where('verif', $vstat)
                ->skip($start)
                ->take($count)
                ->get();
            $pld = [];
            foreach ($members as $member) {
                $diod = $member->diocese_id;
                $genData = diocese_general_data::where('diocese_id', $diod)->first();
                $pld[] = [
                    'b'=> $member,
                    'g'=> $genData,
                ];
            }
            return response()->json([
                "status"=> true,
                "message"=> "Retrived the first ".$count." starting at ".$start." position",
                "pld"=> $pld
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/searchMember",
     *     tags={"Admin"},
     *     summary="Search for a diocese",
     *     description="ADMIN: Use this endpoint to do a full-text search on diocese records (It will search accross nane and phone)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=true,
     *         description="What to search for",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function searchMember(){
        $pd1 = auth()->payload()->get('pd1');
        if ( $pd1!=null  && $pd1=='1') { //Can read from dir
            $search = null;
            if(request()->has('search')) {
                $search = request()->input('search');
            }
            if($search) {
                $members = diocese_basic_data::whereRaw("MATCH(name, phn) AGAINST(? IN BOOLEAN MODE)", [$search])
                ->orderByRaw("MATCH(name, phn) AGAINST(? IN BOOLEAN MODE) DESC", [$search])
                ->take(5)
                ->get();
                $pld = [];
                foreach ($members as $member) {
                    $diocese_id = $member->diocese_id;
                    $genData = diocese_general_data::where('diocese_id', $diocese_id)->first();
                    $pld[] = [
                        'b'=> $member,
                        'g'=> $genData,
                    ];
                }
                return response()->json([
                    "status"=> true,
                    "message"=> "Success",
                    "pld"=> $pld
                ]); 
            }
            return response()->json([
                "status"=> false,
                "message"=> "The Search param is required"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/searchPayment/{payId}",
     *     tags={"Admin"},
     *     summary="Search for a diocese",
     *     description="ADMIN: Use this endpoint to do a full-text search on diocese records (It will search accross nane and phone)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="payId",
     *         in="path",
     *         required=true,
     *         description="Type of payment to search for",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=true,
     *         description="What to search for",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */

    public function searchPayment($payId){
        if ( $this->permOk('pp1')) { //Can read from pay
            $search = null;
            if(request()->has('search')) {
                $search = request()->input('search');
            }
            if($search) {
                $pld = null;
                if( $payId=='0' ){
                    $pld = pays0::whereRaw("MATCH(name, ref, diocese_id) AGAINST(? IN BOOLEAN MODE)", [$search])
                    ->orderByRaw("MATCH(name, ref, diocese_id) AGAINST(? IN BOOLEAN MODE) DESC", [$search])
                    ->take(5)
                    ->get();
                }
                if( $payId=='1' ){
                    $pld = pays1::whereRaw("MATCH(name, ref, diocese_id) AGAINST(? IN BOOLEAN MODE)", [$search])
                    ->orderByRaw("MATCH(name, ref, diocese_id) AGAINST(? IN BOOLEAN MODE) DESC", [$search])
                    ->take(5)
                    ->get();
                }
                if( $payId=='9' ){
                    $pld = pays9::whereRaw("MATCH(name, ref, diocese_id) AGAINST(? IN BOOLEAN MODE)", [$search])
                    ->orderByRaw("MATCH(name, ref, diocese_id) AGAINST(? IN BOOLEAN MODE) DESC", [$search])
                    ->take(5)
                    ->get();
                }
                return response()->json([
                    "status"=> true,
                    "message"=> "Success",
                    "pld"=> $pld
                ]); 
            }
            return response()->json([
                "status"=> false,
                "message"=> "The Search param is required"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }


    /**
     * @OA\Post(
     *     path="/api/setNacddedInfo",
     *     tags={"Admin"},
     *     summary="Set Nacdded Info",
     *     description="ADMIN: Use this endpoint to set information about Nacdded.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", description="Email of Nacdded"),
     *             @OA\Property(property="cname", type="string", description="Name of the organization"),
     *             @OA\Property(property="regno", type="string", description="Registration number of the organization"),
     *             @OA\Property(property="addr", type="string", description="Address of the organization"),
     *             @OA\Property(property="nationality", type="string", description="Nationality of the organization"),
     *             @OA\Property(property="state", type="string", description="State of the organization"),
     *             @OA\Property(property="lga", type="string", description="Local Government Area of the organization"),
     *             @OA\Property(property="aname", type="string", description="Name of the authorized person"),
     *             @OA\Property(property="anum", type="string", description="Contact number of the authorized person"),
     *             @OA\Property(property="bnk", type="string", description="Bank name"),
     *             @OA\Property(property="pname", type="string", description="Name of the person making the payment"),
     *             @OA\Property(property="peml", type="string", description="Email of the person making the payment"),
     *             @OA\Property(property="pphn", type="string", description="Phone number of the person making the payment"),
     *             @OA\Property(property="paddr", type="string", description="Address of the person making the payment"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Nacdded info set successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setNacddedInfo(Request $request){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $request->validate([
                "email"=>"required",
                "cname"=>"required",
                "regno"=> "required",
                "addr"=> "required",
                "nationality"=>"required",
                "state"=> "required",
                "lga"=> "required",
                "aname"=>"required",
                "anum"=> "required",
                "bnk"=> "required",
                "pname"=>"required",
                "peml"=> "required",
                "pphn"=> "required",
                "paddr"=>"required",
                
            ]);
            nacdded_info::updateOrCreate(
                ["email"=> $request->email,],
                [
                "cname"=> $request->cname,
                "regno"=> $request->regno,
                "addr"=> $request->addr,
                "nationality"=> $request->nationality,
                "state"=> $request->state,
                "lga"=> $request->lga,
                "aname"=> $request->aname,
                "anum"=> $request->anum,
                "bnk"=> $request->bnk,
                "pname"=> $request->pname,
                "peml"=> $request->peml,
                "pphn"=> $request->pphn,
                "paddr"=> $request->paddr,
            ]);
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "NACDDED Info updated"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    /**
     * @OA\Get(
     *     path="/api/getNacddedInfo",
     *     tags={"Admin"},
     *     summary="Get Nacdded Info",
     *     description="ADMIN: Use this endpoint to get information about Nacdded.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Email of Nacdded",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getNacddedInfo(){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            if(request()->has('email')) {
                $eml = request()->input('email');
                $pld = nacdded_info::where('email', $eml)->first();
                if($pld){
                    return response()->json([
                        "status"=> true,
                        "message"=> "Success",
                        "pld"=> $pld,
                    ]);
                }
                return response()->json([
                    "status"=> false,
                    "message"=> "No Data Yet",
                ]);
            }
            return response()->json([
                "status"=> false,
                "message"=> "Email required",
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);   
    }

    /**
     * @OA\Post(
     *     path="/api/setMySchool",
     *     tags={"Api"},
     *     summary="Create/Update School",
     *     description="Use this endpoint to create/update a school.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="diocese_id", type="string", description="The Diocess ID for this school. Get diocese id from getSecretaryInfo endpoint"),
     *             @OA\Property(property="name", type="string", description="Name of school"),
     *             @OA\Property(property="type", type="string", description="Type of school"),
     *             @OA\Property(property="lea", type="string", description="LEA of school"),
     *             @OA\Property(property="addr", type="string", description="Address of school"),
     *             @OA\Property(property="email", type="string", description="School's email"),
     *             @OA\Property(property="phone", type="string", description="School's phone number"),
     *             @OA\Property(property="p_name", type="string", description="Proprietor's name"),
     *             @OA\Property(property="p_email", type="string", description="Proprietor's email"),
     *             @OA\Property(property="p_phone", type="string", description="Proprietor's phone number"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setMySchool(Request $request){
        $request->validate([
            "diocese_id"=>"required",
            "name"=> "required",
            "type"=> "required",
            "lea"=>"required",
            "addr"=> "required",
            "email"=> "required|email",
            "phone"=>"required",
            "p_name"=> "required",
            "p_email"=> "required",
            "p_phone"=>"required",
        ]);
        schools::create([
            "diocese_id"=>$request->diocese_id,
            "name"=> $request->name,
            "type"=> $request->type,
            "lea"=>$request->lea,
            "addr"=> $request->addr,
            "email"=> $request->email,
            "phone"=>$request->phone,
            "p_name"=> $request->p_name,
            "p_email"=> $request->p_email,
            "p_phone"=>$request->p_phone,
        ]);
        return response()->json([
            "status"=> true,
            "message"=> "Success"
        ]);
    }


     /**
     * @OA\Get(
     *     path="/api/getMySchools/{dioceseId}",
     *     tags={"Api"},
     *     summary="Get Schools belonging to my diocese",
     *     description="Use this endpoint to get schools belonging to a secretary's diocese",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="dioceseId",
     *         in="path",
     *         required=true,
     *         description="Diocese ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=false,
     *         description="Start index for limiting the result. If not provided, will return first 20 records only",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getMySchools($dioceseId){
        $start = 0;
        $count = 20;
        if(request()->has('start') && request()->has('count')) {
            $start = request()->input('start');
            $count = request()->input('count');
        }
        $pld = schools::where('diocese_id', $dioceseId)->skip($start)->take($count)->get();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld
        ]); 
    }

    /**
     * @OA\Get(
     *     path="/api/getSchools",
     *     tags={"Admin"},
     *     summary="ADMIN: Get all Schools",
     *     description="Use this endpoint to get all schools. Limit by `start` and `count`",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=false,
     *         description="Start index for limiting the result. If not provided, will return first 20 records only",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=false,
     *         description="Number of records to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getSchools(){
        $start = 0;
        $count = 20;
        if(request()->has('start') && request()->has('count')) {
            $start = request()->input('start');
            $count = request()->input('count');
        }
        $pld = schools::take($count)->skip($start)->get();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld
        ]); 
    }


    //------------------------------------

    //Refresh Token API (GET)
    public function refreshToken(){
        $newToken = auth()->refresh();
        return response()->json([
            "status"=> true,
            "message"=> "New token generated",
            "token"=> $newToken,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/checkTokenValidity",
     *     tags={"Api"},
     *     summary="Check if user is still logged in",
     *     description="No params needed except bearer token. If you get a 200, the token is still valid",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function checkTokenValidity()
    {
        return response()->json([
            "status"=> true,
            "message"=> "Token OK",
        ]);
    }

    //Logout API (GET)
    public function logout(){
        auth()->logout();
        return response()->json([
            "status"=> true,
            "message"=> "Logout successful",
        ]);
    }

    //---NON ENDPOINTS

    public function permOk($pid): bool
    {
        // $pp = auth()->payload()->get($pid);
        // return $pp!=null  && $pp=='1';
        return true;
    }

    public function hasRole($rid): bool
    {
        // $role = auth()->payload()->get('role');
        // return $role!=null  && $role==$rid;
        return true;
    }

}
