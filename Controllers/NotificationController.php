<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserApplicat;
use App\Models\UniDetails;
use App\Models\TbNotification;

use Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Auth;
use Illuminate\Support\Facades\Input;
//use DB;

class NotificationController extends Controller
{
 
	public function ajaxCheckReadNotification(Request $request){
		
		$notify_url = url('/read_seen_notication');
		
		$notify_last_id = $request->input('notify_last_id');
		if(empty($notify_last_id))
		{
			return response()->json(['status'=>'0', 'message'=>'notify_last_id field empty!']);
		}
 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $notify_url.'?notify_last_id='.$notify_last_id ); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		echo "</pre>"; print_r($server_output); echo "</pre>";
		echo "Done"; die;		 
	}
  
  
    public function read_seen_notication(Request $request)
    {
		$notify_last_id = $_GET['notify_last_id'];
		if(empty($notify_last_id))
		{
			return response()->json(['status'=>'0', 'message'=>'notify_last_id field empty curl!']);
		}
		
		$notif_seen_ids = TbNotification::select('id')->where([['id', '<=', $notify_last_id],['read', '0']])->orderBy('id', 'DESC')->get();

		if(!empty($notif_seen_ids))
		{
			foreach($notif_seen_ids as $up_id)
			{
				$up_notif_seen_ids = TbNotification::where('id', $up_id["id"])->update(['read' => 1]);
			} 
			return response()->json(['status'=>'1', 'message'=>'All are set seen successfully!']);		
		}
		else{ 
			return response()->json(['status'=>'0', 'message'=>'All are already seen by admin!']);		
		} 
    }
	
	public function check_notication(Request $request)
    {
		$notification = TbNotification::find(1)->getUserName()->get();
	 
		dd($notification);
		if(!empty($notification))
		{
			echo "1<pre>"; print_r($notification); echo "</pre>"; die;
		}
	 
		echo "2<pre>"; print_r($notification); echo "</pre>";
		die;
		

		/*$notification = TbNotification::select('id', 'user_id', 'app_name', 'noti_type', 'read', 'created_at')->orderBy('id', 'DESC')->get();
		if(!empty($notification))
		{
			echo "1<pre>"; print_r($notification); echo "</pre>"; die;
		}
	 
		echo "2<pre>"; print_r($notification); echo "</pre>";
		die;*/
	}



}

