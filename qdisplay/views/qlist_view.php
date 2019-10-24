<?php
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
     && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	 //print_r($user_list);
     echo '<li>';
     if (isset($doctor_info["room_number"])){
        echo '<a href="javascript:void(0);">You are &nbsp;in room <span class="label label-success">'.$doctor_info["room_number"].' / '.$doctor_info["doctor_number"].'</span>&nbsp;<button class="btn btn-default btn-xs" onclick="self.document.location=\''.site_url("qdisplay/change_room_info/".$doctor_info["opd_room_id"]).'\'">Change</button></a>';
		//self.document.location=\''.site_url("appointment/room_info/".$doctor_info["opd_room_id"]).'\'
        if (isset($current_token_info) &($current_token_info["APPID"]>0)){
            if ($this->config->item("calling_OPD_qdisplay") == "AUTO"){
                echo '<a href="javascript:void(0);" onclick="recall_token('.$current_token_info["APPID"].');"><b style="color:red">Re-Call token ('.$current_token_info["token"].')</b></a>';
                echo '<a href="javascript:void(0);" onclick="cancel_token('.$current_token_info["APPID"].');"><b style="color:red">Call next patient</b></a>';
                echo '<a href="javascript:void(0);" onclick="stop_calling();"><b style="color:red">Stop calling ! </b></a>';
            }
            else if ($this->config->item("calling_OPD_qdisplay") == "MANUAL"){
                echo '<a href="javascript:void(0);" onclick="recall_token('.$current_token_info["APPID"].');"><b style="color:red">Re-Call token ('.$current_token_info["token"].')</b></a>';
                echo '<a href="javascript:void(0);" onclick="call_next_patient_to('.$doctor_info["opd_room_id"].','.$doctor_info["doctor_id"].',\''.$doctor_info["serving_type"].'\')"><b style="color:green">Call next patient</b></a>';
            }
        }
		else{
            if ($this->config->item("calling_OPD_qdisplay") == "AUTO"){
                echo '<a href="javascript:void(0);" onclick="start_calling('.$doctor_info["opd_room_id"].','.$doctor_info["doctor_id"].',\''.$doctor_info["serving_type"].'\')"><b style="color:green">Start calling patients</b></a>';

            }
            else if ($this->config->item("calling_OPD_qdisplay") == "MANUAL"){
                echo '<a href="javascript:void(0);" onclick="call_next_patient_to('.$doctor_info["opd_room_id"].','.$doctor_info["doctor_id"].',\''.$doctor_info["serving_type"].'\')"><b style="color:green">Call next patient</b></a>';

            }
		}
     }   
     else{
         echo '<a href="javascript:void(0);">Your are not assigned to any room<button class="btn btn-default btn-xs" onclick="self.document.location=\''.site_url("qdisplay/change_room_info/").'\'">Edit</button></a>';

         }
    echo '</li>';	
 	
}

else{
	echo 'Nothing here';
}
?>