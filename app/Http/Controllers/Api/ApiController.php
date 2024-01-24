<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SSSMails;
use App\Models\admin_user;
use App\Models\adsi_info;
use App\Models\announcements;
use App\Models\diocese_basic_data;
use App\Models\diocese_general_data;
use App\Models\events;
use App\Models\payment_refs;
use App\Models\pays0;
use App\Models\pays1;
use App\Models\pays2;
use App\Models\secretary_data;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    //Register API (POST, formdata)
    public function register(Request $request){
        //Data validation
        $request->validate([
            "email"=>"required|email|unique:users",
            "password"=> "required",
        ]);
        //Save Data to DB
        User::create([
            "email"=> $request->email,
            "password"=> bcrypt($request->password),
        ]);
        $token = JWTAuth::attempt([
            "email"=> $request->email,
            "password"=> $request->password,
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

    //Login API (POST, formdata)
    public function login(Request $request){
        //Data validation
        $request->validate([
            "email"=>"required|email",
            "password"=> "required",
        ]);
        $mid = $request->memid;
        $phn = $request->phn;
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
        Log::info('Paystack hooked ' . json_encode($request->all()));
        $secret = 'sk_test_b3a8e08803d112049764495a5e08168d6514785f';
        $computedHash = hash_hmac('sha512', $request->getContent(), $secret);// Dont use json_encode($request->all()) in hashing
        if ($computedHash == $request->header('x-paystack-signature')) {
            $payload = json_decode($request->getContent(), true);
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
                            "memid"=>$payinfo[3],
                            "ref"=> $ref,
                            "name"=> $nm,
                            "time"=> $tm,
                        ];
                        if($payinfo[1]=='0'){
                            pays0::create($upl);
                            member_basic_data::where("memid", $payinfo[3])->update(['pay' => '1']);
                        }else if ($payinfo[1]=='1'){
                            $yr = $payload['data']['metadata']['year'];
                            $upl['year'] = $yr;
                            pays1::create($upl);
                        }else{ // ie 2
                            $sh = $payload['data']['metadata']['shares'];
                            $upl['shares'] = $sh;
                            pays2::create($upl);
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
        } else {
            Log::info('Invalid hash '.$request->header('x-paystack-signature'));
            Log::info('Computed '.$computedHash);
            // Request is invalid
            return response()->json(['status' => 'error'], 401);
        }
    }

    //---Protected from here

    public function authAsAdmin(){
        $user = auth()->user();
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
            $token = JWTAuth::customClaims($customClaims)->fromUser(auth()->user());
            return response()->json([
                "status"=> true,
                "message"=> "Admin authorization granted",
                "token"=> $token,
            ]);
        }
        // Respond
        return response()->json([
            "status"=> false,
            "message"=> "Failed"
        ]);
    }



    //GET
    public function getAnnouncements(){
        $pld = announcements::all();
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }

    //POST (FILES)
    public function uploadFile(Request $request){
        $request->validate([
            'file' => 'required', //|mimes:jpeg,png,jpg,gif,svg|max:2048
            'filename' => 'required',
            'folder' => 'required',
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $request->filename;
            $folder = $request->folder;
            $file->move(public_path('uploads/'.$folder), $filename);
            // Respond
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

    //GET (FILE)
    public function getFile($folder,$filename){
        $filePath = public_path('uploads/'.$folder.'/'.$filename);
        if (file_exists($filePath)) {
            return response()->file($filePath);
        } else {
            return response()->json([
                "status" => false,
                "message" => "File not found",
            ], 404);
        }
    }

    

    //GET (FILE)
    public function fileExists($folder,$filename){
        $filePath = public_path('uploads/'.$folder.'/'.$filename);
        if (file_exists($filePath)) {
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

    //GET
    public function getEvents(){
        $count = 0;
        if(request()->has('count')) {
            $count = request()->input('count');
        }
        $pld = null;
        if($count == 0){
            $pld = events::all();
        }else{
            $pld = events::latest()->take($count)->get();
        }
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);
    }

    //Profile API (POST)
    public function setDioceseBasicInfo(Request $request){
        $request->validate([
            "email"=>"required|email",
            "name"=> "required",
            "phn"=> "required",
            "pwd"=> "required",
        ]);
        $usr = User::where("email", $request->email)->first();
        if($usr){
            $usr->update([
                "password"=>bcrypt($request->pwd),
            ]);            
            diocese_basic_data::updateOrCreate(
                ["email"=> $request->email,],
                [
                "name"=> $request->name,
                "phn"=> $request->phn,
                "pwd"=> $request->pwd,
            ]);
            return response()->json([
                "status"=> true,
                "message"=> "Success. Please login again"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "The email does not exist"
        ]);
    }


    //GET
    public function getDioceseBasicInfo(){
        if(request()->has('email')) {
            $eml = request()->input('email');
            $pld = diocese_basic_data::where("email", $eml)->first();
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


    //Profile API (POST)
    public function setDioceseGeneralInfo(Request $request){
        $request->validate([
            "email"=>"required",
            "state"=> "required",
            "lga"=> "required",
            "addr"=> "required",
        ]);
        diocese_general_data::updateOrCreate(
            ["email"=> $request->email,],
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

    public function getDioceseGeneralInfo(){
        if(request()->has('email')) {
            $eml = request()->input('email');
            $pld = diocese_general_data::where("email", $eml)->first();
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


    public function setSecretaryInfo(Request $request){
        $request->validate([
            "email"=>"required",
            "fname"=> "required",
            "lname"=> "required",
            "mname"=> "required",
            "sex"=> "required",
            "phn"=> "required",
            "addr"=> "required",
        ]);
        secretary_data::updateOrCreate(
            ["email"=> $request->email,],
            [
            "fname"=> $request->fname,
            "lname"=> $request->lname,
            "mname"=> $request->mname,
            "sex"=> $request->sex,
            "phn"=> $request->phn,
            "addr"=> $request->addr,
        ]);
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "Success"
        ]);
    }

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






    //--------------- ADMIN CODES

    public function setFirstAdminUserInfo(){
        admin_user::updateOrCreate(
            ["memid"=> '11111111',],
            [
            "lname"=> 'ADSI',
            "oname"=> 'Stable Shield',
            "eml"=> 'admin@adsicoop.com.ng',
            "role"=> '0',
            "pd1"=> '1',
            "pd2"=> '1',
            "pp1"=> '1',
            "pp2"=> '1',
            "pm1"=> '1',
            "pm2"=> '1',
            
        ]);
        // Respond
        return response()->json([
            "status"=> true,
            "message"=> "First Admin User Created"
        ]);
    }


    //POST
    public function setAdminUserInfo(Request $request){
        $request->validate([
            "email"=>"required|email",
            "lname"=> "required",
            "oname"=> "required",
            "role"=> "required",
            "pd1"=> "required",
            "pd2"=> "required",
            "pw1"=> "required",
            "pw2"=> "required",
            "pp1"=> "required",
            "pp2"=> "required",
            "pm1"=> "required",
            "pm2"=> "required",
        ]);
        admin_user::updateOrCreate(
            ["email"=> $request->email,],
            [
            "lname"=> $request->lname,
            "oname"=> $request->oname,
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
            "message"=> "Admin User Info updated"
        ]);
    }

    //GET 
    public function getHighlights(){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $totalUsers = User::count();
            $totalMales = member_general_data::where('sex', 'M')->count();
            $totalFemales = member_general_data::where('sex', 'F')->count();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> [
                    'totalUsers'=>$totalUsers,
                    'totalMales'=>$totalMales,
                    'totalFemales'=> $totalFemales
                ],
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    //POST
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

     //GET
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

    //GET
    public function getAdmin($adminId){
        $role = auth()->payload()->get('role');
        if ( $role!=null) { //Granted to all admin as is needed on first page
            $pld = admin_user::where('memid', $adminId)->first();
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

    //POST
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

    //GET
    public function removeAdmin($adminId){
        $role = auth()->payload()->get('role');
        if ( $role!=null && $role=='0') {
            $dels = admin_user::where('memid', $adminId)->delete();
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
            "message"=> "Access denied"
        ],401);
    }

    //POST 
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

    //POST
    public function setEvents(Request $request){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
              $request->validate([
                "title"=>"required",
                "time"=> "required",
                "venue"=> "required",
                "fee"=> "required",
            ]);
            events::create([
                "title"=> $request->title,
                "time"=> $request->time,
                "venue"=> $request->venue,
                "fee"=> $request->fee,
            ]);
            // Respond
            return response()->json([
                "status"=> true,
                "message"=> "Event Added"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }











































    
    

    
    

    //GET
    public function getMemPays($memid){
        $shares = pays2::where('memid', $memid)->get();
        $dues = pays1::where('memid', $memid)->get();
        $pld = [
            's'=> $shares,
            'd'=> $dues,
        ];
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $pld,
        ]);  
    }

    //GET
    public function getMemDuesByYear($memid, $year){
        $dues = pays1::where('memid', $memid)->where('year', $year)->first();
        return response()->json([
            "status"=> true,
            "message"=> "Success",
            "pld"=> $dues,
        ]);  
    }

    //Files

    

    

    //-- ADMIN

    //GET 
    public function getVerificationStats(){
        $pd1 = auth()->payload()->get('pd1');
        if ( $pd1!=null  && $pd1=='1') { //Can read from dir
            $totalVerified = member_basic_data::where('verif', '1')->count();
            $totalUnverified = member_basic_data::where('verif', '0')->count();
            return response()->json([
                "status"=> true,
                "message"=> "Success",
                "pld"=> [
                    'totalVerified'=>$totalVerified,
                    'totalUnverified'=>$totalUnverified
                ],
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    //GET
    public function getMembersByV($vstat){
        $start = 0;
        $count = 20;
        if(request()->has('start') && request()->has('count')) {
            $start = request()->input('start');
            $count = request()->input('count');
        }
        $pd1 = auth()->payload()->get('pd1');
        if ( $pd1!=null  && $pd1=='1') { //Can read from dir
            $members = member_basic_data::where('verif', $vstat)
                ->skip($start)
                ->take($count)
                ->get();
            $pld = [];
            foreach ($members as $member) {
                $memid = $member->memid;
                $genData = member_general_data::where('memid', $memid)->first();
                $pld[] = [
                    'b'=> $member,
                    'g'=> $genData,
                ];
            }
            return response()->json([
                "status"=> true,
                "message"=> "Retrived the first $count starting at $start position",
                "pld"=> $pld
            ]);   
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    //POST
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
                "memid"=>$payinfo[3],
                "ref"=> $ref,
                "name"=> $nm,
                "time"=> $tm,
            ];
            if($payinfo[1]=='0'){
                pays0::create($upl);
                member_basic_data::where("memid", $payinfo[3])->update(['pay' => '1']);
            }else if ($payinfo[1]=='1'){
                $yr = $request->year;
                $upl['year'] = $yr;
                pays1::create($upl);
            }else{ // ie 2
                $sh = $request->shares;
                $upl['shares'] = $sh;
                pays2::create($upl);
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

     //GET
     public function getPayments($payId){
        $pp1 = auth()->payload()->get('pp1');
        if ( $pp1!=null  && $pp1=='1') { //Can read from dir
            $pld = null;
            if( $payId=='0' ){
                $pld = pays0::all();
            }
            if( $payId=='1' ){
                $pld = pays1::all();
            }
            if( $payId=='2' ){
                $pld = pays2::all();
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

    //POST
    public function setAdsiInfo(Request $request){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $request->validate([
                "memid"=>"required",
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
            adsi_info::updateOrCreate(
                ["memid"=> $request->memid,],
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
                "message"=> "ADSI Info updated"
            ]);
        }
        return response()->json([
            "status"=> false,
            "message"=> "Access denied"
        ],401);
    }

    //GET
    public function getAsdiInfo(){
        $role = auth()->payload()->get('role');
        if ( $role!=null  && $role=='0') {
            $pld = adsi_info::where('memid', '11111111')->first();
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
            "message"=> "Access denied"
        ],401);   
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

    //IF reached, token is still valid!, GET
    public function checkTokenValidity(Request $request)
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

}
