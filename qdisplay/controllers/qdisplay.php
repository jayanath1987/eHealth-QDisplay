<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
--------------------------------------------------------------------------------
HHIMS - Hospital Health Information Management System
Copyright (c) 2011 Information and Communication Technology Agency of Sri Lanka
<http: www.hhims.org/>
----------------------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify it under the
terms of the GNU Affero General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,but WITHOUT ANY 
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR 
A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along 
with this program. If not, see <http://www.gnu.org/licenses/> or write to:
Free Software  HHIMS
ICT Agency,
160/24, Kirimandala Mawatha,
Colombo 05, Sri Lanka
---------------------------------------------------------------------------------- 
Author: Author: Mr. Jayanath Liyanage   jayanathl@icta.lk
                 
URL: http://www.govforge.icta.lk/gf/project/hhims/
----------------------------------------------------------------------------------
*/
class Qdisplay extends MX_Controller {
	function __construct(){
		parent::__construct();
		//$this->checkLogin();
		$this->load->library('session');
		$this->load->helper('text');
		$this->load->helper('url');
		$this->load->model('mqdisplay');
	}
		
	public function procedure_room(){
		$data["config"] = $this->mqdisplay->get_config(3);
		$data["hospital_info"] = $this->mqdisplay->get_hospital_info();
		$data["q_data"]["counter_list"] = $this->mqdisplay->get_procedure_room_list();
		$this->load->vars($data);
		$this->load->view('qdisplay_procedure_room_view');	
	}
        
        public function laboratory(){
		$data["config"] = $this->mqdisplay->get_config(3);
		$data["hospital_info"] = $this->mqdisplay->get_hospital_info();
		$data["q_data"]["counter_list"] = $this->mqdisplay->get_laboratory_list();
                //die(print_r($data["q_data"]["counter_list"]));
		$this->load->vars($data);
		$this->load->view('qdisplay_laboratory_view');
	}

	
	public function get_procedure_room_queue (){
		$data["q"] = $this->mqdisplay->get_procedure_room_queue();
        echo json_encode( $data["q"]);
	}
        
        public function get_laboratory_queue (){
		$data["q"] = $this->mqdisplay->get_laboratory_queue();
                echo json_encode( $data["q"]);
	}
        
	public function pharmacy(){
		$data["config"] = $this->mqdisplay->get_config(2);
		$data["hospital_info"] = $this->mqdisplay->get_hospital_info();
		$data["q_data"]["counter_list"] = $this->mqdisplay->get_counter_list();
		$this->load->vars($data);
		$this->load->view('qdisplay_pharmacy_view');	
	}
	
	public function get_pharmacy_queue (){
		$data["q"] = $this->mqdisplay->get_prescription_queue();
		if (!empty($data["q"])){
			for ($i=0; $i <count($data["q"]); $i++){
				if ($data["q"][$i]["PID"] >0){
					$token = $this->mqdisplay->get_token(date("Y-m-d"),$data["q"][$i]["PID"]);
					if ($token && count($token)>0){
						$token_str =  '';
						for ($j=0; $j <count($token); $j++){
							$token_str .= $token[$j]["token"].',';
						}
					}
					else{
						$token_str = '';
					}
					if (isset($token_str)){
						$token_str = rtrim($token_str, ",");
						$data["q"][$i]["token"] = $token_str;
					}
				}
			}
		}
        echo json_encode( $data["q"]);
	}
	
	public function get_doctor_room_seatData(){
		$data["q_data"]["room_list"] = $this->mqdisplay->get_room_list();
                $data["q_data"]["doctor_list"] = $this->mqdisplay->get_doctor_list();
		echo json_encode($data["q_data"]);

	}
    public function mark_skipped($appid){
		if(!isset($appid) ||(!is_numeric($appid) )){
			$data["error"] = "Appointment not found";
			$this->load->vars($data);
			$this->load->view('appointment_error');	
			return;
		}
		$this->load->model('mpersistent');
                $appointment_info = $this->mpersistent->open_id($appid,"appointment","APPID");
		$sve_data = array(
			"status"=>"SKIPPED",
            "opd_room_id"=>NULL,"Consultant"=>NULL
		);
        $res = $this->mpersistent->update("appointment","APPID",$appid,$sve_data );
        if ($appointment_info["Consultant"]){
                $this->get_next_patient_to_doctor($appointment_info["Consultant"]);
        }
		//echo Modules::run('appointment/process/',$appid);
        redirect(site_url("appointment/?mid=16"));
    }

    public function skip_tokens(){
         $this->load->model('mpersistent');
        $skip_time = $this->config->item("skip_time");
        if (!$skip_time){
            $skip_time = 1;
        }
        $q_list = $this->mqdisplay->get_skip_pending($skip_time);
        if(!empty($q_list)){
            for ($i=0; $i<count($q_list); $i++){
                $this->mpersistent->update("appointment","APPID",$q_list[$i]["APPID"],array("status"=>"SKIPPED","opd_room_id"=>NULL,"Consultant"=>NULL));
                if ($q_list[$i]["Consultant"]){
                        $this->get_next_patient_to_doctor($q_list[$i]["Consultant"]);
                }
            }
        }   
        redirect(site_url("appointment/?mid=16"));
    }
    
    public function index()
	{
		//$data = $this->get_doctor_room_seatData();
                //$this->load->model('mqdisplay');
		$this->load->model('mopd_visits');
		$data["pt_count"]=$this->mopd_visits->count_todays_visit();
                $data["q_data"]["room_list"] = $this->mqdisplay->get_room_list();
                $data["q_data"]["doctor_list"] = $this->mqdisplay->get_doctor_list();
		$data["config"] = $this->mqdisplay->get_config(1);
		$data["hospital_info"] = $this->mqdisplay->get_hospital_info();
		//$data["patient_list"] = $this->mqdisplay->get_room_list();
		//var_dump( $data);exit;
		$this->load->vars($data);
		$this->load->view('qdisplay_view');	
	}
	
	public function edit($id=null)
	{	
		header("Location: ".site_url('appointment')); 
    }
    
    function get_queue(){
        /* for auto clearing the q
        $this->load->model('mpersistent');
        $skip_time = $this->config->item("skip_time");
        if (!$skip_time){
            $skip_time = 1;
        }
        $q_list = $this->mqdisplay->get_skip_pending($skip_time);
        if(!empty($q_list)){
            for ($i=0; $i<count($q_list); $i++){
                $this->mpersistent->update("appointment","APPID",$q_list[$i]["APPID"],array("status"=>"SKIPPED"));
                if ($q_list[$i]["Consultant"]){
                    $this->get_next_patient_to_doctor($q_list[$i]["Consultant"]);
                }
            }
        }
        */
        $data["q"] = $this->mqdisplay->get_queue();
        echo json_encode( $data["q"]);
    }
     
	public function take_new_seat ($room_id){
		$this->load->model('mpersistent');
		$this->mpersistent->delete($this->session->userdata('UID'),"opd_room_doctor","doctor_id");
		$seat_num = $this->mqdisplay->get_next_seat($room_id)+1;
		$res = $this->mpersistent->create("opd_room_doctor", array( "doctor_id" =>  $this->session->userdata('UID'), "opd_room_id"=>$room_id,"doctor_number"=>$seat_num,"Active"=>1 ));
		if ($res >0){
		 echo $seat_num;
		}
		else{
			echo -1;
		}
	}	
	public function free_my_seat($dr_id){
        $this->load->model('mpersistent');
		$this->mpersistent->delete($dr_id,"opd_room_doctor","doctor_id");
    }
    
	public function take_seat($opd_room_doctor_id,$room_id){
		$this->load->model('mpersistent');
		$this->mpersistent->delete($this->session->userdata('UID'),"opd_room_doctor","doctor_id");
		$seat_num = $this->mqdisplay->get_next_seat($room_id)+1;
		$res = $this->mpersistent->create("opd_room_doctor", array( "doctor_id" =>  $this->session->userdata('UID'), "opd_room_id"=>$room_id,"doctor_number"=>$seat_num,"Active"=>1 ));
		if ($res >0){
		 echo $seat_num;
		}
		else{
			echo -1;
		}
	}	
	public function change_room_info($opd_room_id=null){
            
                $wt = $this->mqdisplay->get_workstation($this->session->userdata("WT"));
              
                if($wt=='OPD'){
                    
		$data["dr_info"] = $this->session->userdata("FullName");
                $data["room_list"] = $this->mqdisplay->get_room_list();
		$data["seat_list"] = $this->mqdisplay->get_doctor_list();
		$this->load->vars($data);
                $this->load->view('room_change_steps_view');
                
                }
                else{
                    
                  redirect(site_url("patient/search"));  
                    
                }
                
	}
	
    public function get_q_list(){
        $data["doctor_info"]= $this->mqdisplay->get_doctor_info($this->session->userdata('UID') );
        if (isset($data["doctor_info"]["opd_room_id"])){
            $data["current_token_info"]= $this->mqdisplay->get_current_token_info($data["doctor_info"]["opd_room_id"],$this->session->userdata('UID') );
        }
        //print_r($data["current_token_info"]);
        $this->load->vars($data);
        $this->load->view('qlist_view');	
    }
    
    public function make_serverd(){
		if(is_numeric($_POST["max_token"])){
			$this->mqdisplay->make_serverd($_POST["max_token"]);
			header("Location: ".site_url('appointment')); 
		}
    }

    public function cancel_token($appid){
        $this->load->model('mpersistent');
        $data["appointment_info"] = $this->mpersistent->open_id($appid,"appointment","APPID");
        if ($data["appointment_info"]["APPID"] > 0){
            $sve_data = array(
                "status"=>"CANCELLED"
            );
           $res = $this->mpersistent->update("appointment","APPID",$data["appointment_info"]["APPID"],$sve_data );
            if ($this->config->item("calling_OPD_qdisplay") == "AUTO"){
                $this->get_next_patient_to_doctor();
           }
           echo $res;
        }
        else{
            echo -1;
        }
    }

    public function recall_token($appid){
        $this->load->model('mpersistent');
        $data["appointment_info"] = $this->mpersistent->open_id($appid,"appointment","APPID");
        if ($data["appointment_info"]["APPID"] > 0){
            $sve_data = array(
                "status"=>"RECALL"
            );
           $res = $this->mpersistent->update("appointment","APPID",$data["appointment_info"]["APPID"],$sve_data );
           
           echo $res;
        }
        else{
            echo -1;
        }
    }
	
	//v2//
	public function get_next_patient_to_doctor($dr_id = null){
        if ($dr_id){
            $dr_id = $dr_id;
        }
        else{
            $dr_id =$this->session->userdata('UID');
        }
        $dr_room_info  = $this->mqdisplay->get_doctor_info($dr_id);
        if($dr_room_info){
            $data["next_appointment_info"] = $this->mqdisplay->get_next_OPD_appointment($dr_room_info["opd_room_id"]);
            if ($data["next_appointment_info"]["APPID"] > 0){
                $sve_data = array(
					"status"=>"CALL",
					"Consultant"=>$dr_id,
					"opd_room_id"=>$dr_room_info["opd_room_id"]
				);
                $this->mpersistent->update("appointment","APPID",$data["next_appointment_info"]["APPID"],$sve_data );
            }
        }
    }
	//v2//
	public function stop_calling(){
		$this->mqdisplay->stop_calling_for_doctor($this->session->userdata('UID') );
	/*
		$data["doctor_info"]= $this->mqdisplay->get_doctor_info($this->session->userdata('UID') );
        if (isset($data["doctor_info"]["opd_room_id"])){
            $data["current_token_info"]= $this->mqdisplay->get_current_token_info($data["doctor_info"]["opd_room_id"],$this->session->userdata('UID') );
                if (isset($data["current_token_info"]["APPID"])){
                    $this->load->model('mpersistent');
                    $data["appointment_info"] = $this->mpersistent->open_id($data["current_token_info"]["APPID"],"appointment","APPID");
                    if ($data["appointment_info"]["APPID"] > 0){
                        $sve_data = array(
                            "status"=>NULL,
                            "Consultant"=>NULL,
                            "opd_room_id"=>NULL
                        );
                        $this->mpersistent->update("appointment","APPID",$data["appointment_info"]["APPID"],$sve_data );
                    }
                }
        }
		*/
    }
	
	//v2//
	public function start_calling($room,$doctor,$serving_type=null){
        //$this->load->model('mappointment');
        $this->load->model('mpersistent');
        $this->mqdisplay->complete_previous_appointments($doctor);
        if ((!$serving_type) OR ($serving_type == 'OPD')){
            $data["next_appointment_info"] = $this->mqdisplay->get_next_OPD_appointment();
            if ($data["next_appointment_info"]["APPID"] > 0){
                $sve_data = array(
					"status"=>"CALL",
					"Consultant"=>$doctor,
					"opd_room_id"=>$room
				);
               $res = $this->mpersistent->update("appointment","APPID",$data["next_appointment_info"]["APPID"],$sve_data );
               echo $res;
            }
            else{
                echo -1;
            }
        }
        else{
        }    
    }
 

	
    public function call_next_patient($room,$doctor,$serving_type=null){
        //$this->load->model('mappointment');
        $this->load->model('mpersistent');
        $this->mqdisplay->complete_previous_appointments($doctor);
        if ((!$serving_type) OR ($serving_type == 'OPD')){
            $data["next_appointment_info"] = $this->mqdisplay->get_next_OPD_appointment($room);
            if ($data["next_appointment_info"]["APPID"] > 0){
                $sve_data = array(
					"status"=>"CALL",
					"Consultant"=>$doctor,
					"opd_room_id"=>$room
				);
               $res = $this->mpersistent->update("appointment","APPID",$data["next_appointment_info"]["APPID"],$sve_data );
               echo $res;
            }
            else{
                echo -1;
            }
        }
        else{
        }    
    }
} 


//////////////////////////////////////////

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */

