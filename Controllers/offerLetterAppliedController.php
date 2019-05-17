<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserApplicat;
use App\Models\UniDetails; 
use App\Models\TbDocuments;
use App\Models\TbDocumentImg;
use App\Models\TbOfferLetterApplied;
 

use Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use DB;
use App\Http\Traits\NotificationTrait;

class offerLetterAppliedController extends Controller
{
	use NotificationTrait;
    public function offerLetterApplied(Request $request)
    {
		$ref_id = $request->session()->get('ref_id');
		if(empty($ref_id))
		{
			return redirect('applicat/new_ielts');	
		}
		$requestId = $request->session()->get('request_id');
		$tab_color = $this->applicantBreadcrumb($requestId, $ref_id);		
		$notification = $this->showNoticationBox(); 	 
		
		$letterApplied = TbOfferLetterApplied::where('ref_id', $ref_id)->get();
		if(empty($letterApplied))
		{
			$letterApplied = "";
		}	
		 
		//echo "<pre>"; print_r($letterApplied); echo "</pre>"; //die;
		 
		return view('applicats/offerLetterApplied')->with(array('letterApplied'=> $letterApplied,'requestId'=>$requestId,'tab_color' => $tab_color,'notification' => $notification));
    } 
	
	
	public function ajaxsaveofferletterapplied(Request $request)
    {  
		$ofa_buy_rate = isset($request->ofa_buy_rate)?$request->ofa_buy_rate: '';
		$ofa_sell_rate = isset($request->ofa_sell_rate)?$request->ofa_sell_rate: '';
		$agency_or_direct = isset($request->agency_or_direct)?$request->agency_or_direct: '';
		$agency_name = isset($request->agency_name)?$request->agency_name: '';
		$offer_letter_check = isset($request->offer_letter_check)?$request->offer_letter_check: '';
		$ofa_date_ofreceived = isset($request->ofa_date_ofreceived)?$request->ofa_date_ofreceived: '';
		$coll_uni_name = isset($request->coll_uni_name)?$request->coll_uni_name: '';
		$course_name = isset($request->course_name)?$request->course_name: '';
		
		if(empty($ofa_buy_rate) || empty($ofa_sell_rate) || empty($agency_or_direct) || empty($offer_letter_check))
		{
			return response()->json(['status'=>'0', 'message'=>'Mandatory information is missing!']);	
		}
		
		$ref_id = $request->session()->get('ref_id');
		$createdDate = strtotime("now");
		 
		$letterApplied = new TbOfferLetterApplied; 
		$letterApplied->ref_id = $ref_id;
		$letterApplied->agency_direct = $request->agency_or_direct;
		
		if($request->agency_direct == "direct"){ 
			$letterApplied->agency_name = ""; 
		}
		else{
			$letterApplied->agency_name = $request->agency_name;
		}
		$letterApplied->buy_rate = $request->ofa_buy_rate;
		$letterApplied->sell_rate = $request->ofa_sell_rate;
		
		$letterApplied->applied = $request->offer_letter_check;
		
		if($request->offer_letter_check == "offer_letter_not_applied"){ 
			$letterApplied->coll_uni_name = "";
			$letterApplied->course_name = "";
			$letterApplied->date_ofa = "";
		}
		else{
			$letterApplied->coll_uni_name = $request->coll_uni_name;
			$letterApplied->course_name = $request->course_name;
			$letterApplied->date_ofa = $request->ofa_date_ofreceived;
		}
		  
		$letterApplied->letter_applied_page = '1';
		$letterApplied->created_at = $createdDate;
		
		if($letterApplied->save())
		{
			$ola_id = $letterApplied->id;
			$offer_letter_applied = "";
			$offer_letter_not_applied = "";
			$offer_letter_not_applied_check = "";
			if($request->offer_letter_check == "offer_letter_applied"){
				$offer_letter_applied = "Checked=checked";
			}
			elseif($request->offer_letter_check == "offer_letter_not_applied")
			{
				$offer_letter_not_applied_check = "style=display:none;";
				$offer_letter_not_applied = "Checked=checked";
			}
			$through_agency = "";
			$direct = "";
			$agency_or_direct_check ="";
			if($request->agency_or_direct == "through_agency"){
				$through_agency = "Checked=checked";
			}
			elseif($request->agency_or_direct == "direct")
			{
			
				$agency_or_direct_check = "style=display:none;";
				$direct = "Checked=checked";
			}
			
			$agent1 = "";
			$agent2 = "";
			$agent3 = "";
			$agent4 = "";
			if($agency_name == "Agency 1"){
				$agent1 = "selected";
			}
			elseif($agency_name == "Agency 2")
			{
				$agent2 = "selected";
			}
			elseif($agency_name == "Agency 3"){
				$agent3 = "selected";
			}
			elseif($agency_name == "Agency 4")
			{
				$agent4 = "selected";
			}
			
			
			
				$data = '<div id="offer_letter_box__'.$ola_id.'">
				<div class="form-inline">
					<div class="form-group">
					<label class="radiobox radio-inline" >Applying through Agency
						<input disabled type="checkbox" '.$through_agency.' value="through_agency" name="through_agency_or_direct" />
						<span class="checkmark"></span>
					</label>&nbsp; &nbsp;
					<label class="radiobox radio-inline" style="margin-left:0px;">Direct
						<input disabled type="checkbox"  '.$direct.' value="direct" name="through_agency_or_direct"/>
						<span class="checkmark"></span>
					</label>
				 </div>
					</br></br>
				<div id="select_agency_ola_page" class="form-group pd-lft desofr" '.$agency_or_direct_check.'  style="display: inline-block;">
					<label>Select Agency</label><br>
					<select disabled name="agency">
					  <option value="">Select</option>
					  <option '.$agent1.' value="Agency 1">Agency 1</option>
					  <option '.$agent2.' value="Agency 2">Agency 2</option>
					  <option '.$agent3.' value="Agency 3">Agency 3</option>
					  <option '.$agent4.' value="Agency 4">Agency 4</option>
					</select>
				 </div>
						
				<div id="">
					<label>Offer Letter 1</label>
					<div class="form-group" style="margin-top: 12px;margin-left: 20px;">
						<label class="radiobox radio-inline"style="padding-left:20px;" >Applied
							<input disabled type="checkbox" '.$offer_letter_applied.' value="offer_letter_applied" name="offer_letter_check"/>
							<span class="checkmark"></span>
						</label>&nbsp; &nbsp;
						<label class="radiobox radio-inline" style="margin-left:0px; padding-left:20px;">Not Applied
							<input disabled type="checkbox" '.$offer_letter_not_applied.' value="offer_letter_not_applied" name="offer_letter_check"/>
							<span class="checkmark"></span>
						</label>
					</div></br>
					<div '.$offer_letter_not_applied_check.' >
					<div class="form-group desofr">
						<label>College/University Name</label></br>
						<input disabled value="'.$request->coll_uni_name.'" name="ofa_coll_uni_name" type="text" class="form-control" placeholder="University of Calgary" size="30" />
					</div>
					<div class="form-group desofr" style="margin-left:20px;">
						<label>Name of Course</label></br>
						<input disabled value="'.$request->course_name.'" name="ofa_name_course" type="text" class="form-control" placeholder="Micro Biology" size="30" />
					</div>
					</br>
					</br>
					<div class="st-rm">
					<div class="row">
						<div class="col-md-3">
							 <label>Estimated Date Of Offer</br> Letter Received</label>
						</div>
						<div class="col-md-9">
							<div class="form-group pd-lft inner-addon right-addon">
								<i class="fa fa-calendar" aria-hidden="true"></i>
								<input disabled type="text" name="ofa_date_ofreceived" id="calendar" value="'.$request->ofa_date_ofreceived.'">
								<a href=""><small class="pull-right">REMINDER EVERY 3 DAYS</small></a>
							</div>
						</div>
					</div>
					</div>
					</div>
					</div>	
				</div>
				<a id="OfferLetterDelete__'.$ola_id.'" onclick="OfferLetterDelete('.$ola_id.')" href="javascript:;" data-toggle="tooltip" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
				</div>
				 
				</br> <hr></br>';
		 
				
			return response()->json(['status'=>'1','agency_or_direct'=>$agency_or_direct,'agency_or_direct_check'=>$agency_or_direct_check, 'data'=> $data, 'message'=>'Offer Letter applied successfully']);	
		}
		else{
			return response()->json(['status'=>'0', 'message'=>'Something went wrong']);
		}
	}
	
	
	public function ajaxDeleteOfferLetter(Request $request)
    {
		$id = $request->input('id');
		 
		$OfferLetterDelete = TbOfferLetterApplied::where([["id", $id]])->delete();
		 
		if($OfferLetterDelete == "1")
		{
			return response()->json(['status'=>'1', 'message'=>'Offer Letter deleted successfully!']);
		}
	}
	
	public function ajaxEditOfferLetter(Request $request)
    {
		$id = $request->input('id');
		 
		$getOfferLetter = TbOfferLetterApplied::where([["id", $id]])->get();
		 
		if($getOfferLetter)
		{
			return response()->json(['status'=>'1', 'offerLetterData'=>$getOfferLetter,'message'=>'Show Offer Letter data successfully!']);
		}
		 
	}
	
	public function offerLetterReceived(Request $request)
    {
		$ref_id = $request->session()->get('ref_id');
		if(empty($ref_id))
		{
			return redirect('applicat/new_ielts');	
		}
		$requestId = $request->session()->get('request_id');
		$tab_color = $this->applicantBreadcrumb($requestId, $ref_id);		
		$notification = $this->showNoticationBox(); 	 
		
		 
		 
		//echo "<pre>"; print_r($letterApplied); echo "</pre>"; //die;
		 
		return view('applicats/offerLetterReceived')->with(array('requestId'=>$requestId,'tab_color' => $tab_color,'notification' => $notification));
	 
	}
	
	public function saveofferletterapplied(Request $request)
    {
		 
		//echo "<pre>"; print_r($_POST); echo "</pre>";  
		/*
		$ofa_buy_rate_hide = isset($_POST["ofa_buy_rate_hide"])?$_POST["ofa_buy_rate_hide"]: '';
		$ofa_sell_rate_hide = isset($_POST["ofa_sell_rate_hide"])?$_POST["ofa_sell_rate_hide"]: '';
		$through_angency_or_direct = isset($_POST["through_angency_or_direct"])?$_POST["through_angency_or_direct"]: '';
		$agency = isset($_POST["agency"])?$_POST["agency"]: '';
		$offer_letter_check = isset($_POST["offer_letter_check"])?$_POST["offer_letter_check"]: '';
		$ofa_date_ofreceived = isset($_POST["ofa_date_ofreceived"])?$_POST["ofa_date_ofreceived"]: '';
		
		//if(offer_letter_applied_continue_exist =='0')
		 
		$ref_id = $request->session()->get('ref_id');
		$createdDate = strtotime("now");
		 
		$letterApplied = new TbOfferLetterApplied; 
		$letterApplied->ref_id = $ref_id;
		$letterApplied->agency_direct = $request->through_angency_or_direct;
		
		if($request->through_angency_or_direct == "direct"){ 
			$letterApplied->agency_name = "";
		}
		else{
			$letterApplied->agency_name = $request->agency;
		}
		
		$letterApplied->buy_rate = $request->ofa_buy_rate_hide;
		$letterApplied->sell_rate = $request->ofa_sell_rate_hide;
		 
		$letterApplied->letter_applied_page = '1';
		$letterApplied->created_at = $createdDate;
		 
		$letterApplied->save();
		
		
	
		 
		*/
		echo "hereddd"; die;
    }
	
	
}
