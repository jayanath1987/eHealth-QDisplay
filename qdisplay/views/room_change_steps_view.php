<?php
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

	include("header.php");	///loads the html HEAD section (JS,CSS)
?>
<?php echo Modules::run('menu'); //runs the available menu option to that usergroup ?>
<div class="container" style="width:95%;">
	<div class="row" style="margin-top:55px;">
	  <div class="col-md-2 ">
	  </div>
	  <div class="col-md-10 ">
				 <?php 
					if (isset($error)){
						echo '<div class="alert alert-danger"><b>ERROR:</b>'.$error.'</div>';
						exit;
					}
				?>	
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">Welcome <?php echo $dr_info; ?>You are Logged in as a OPD Doctor</h3>
				    </div>
					<div class="panel-body" style="font-weight:bold;">
                                        
						<!--<div id="step1">
							<span class="badge">1</span> Are you working in OPD? 
							<button class="btn btn-sm btn-success" id="step1_yes">Yes</button>
							<button class="btn btn-sm btn-default" id="step1_no">No</button>
						</div>-->
                                          
						<div id="step1">
							<hr>	
							<span class="badge">1</span> Which room are you in?
							<?php 
								//var_dump($seat_list);
								if (empty($room_list)){
									echo '<span class="alert alert-danger">There is no OPD room defined! Contact HHIMS administrator.</span>';
								}
								else{
									for ($i=0; $i <count($room_list); $i++){
										echo '<input type="button" class="btn btn-sm btn-success" id="room_'.$room_list[$i]["opd_room_id"].'" role="room_select" roomid="'.$room_list[$i]["opd_room_id"].'" value="'.$room_list[$i]["name"].'" >&nbsp;';
									}
								}
							?>
						</div>
						
						<div id="step2">
							<hr>	
							<span class="badge">2</span> Which seat are you in?
							<span id="step2_cont"></span>
						</div>
						
						<div id="step3">
							<hr>	
							<span class="badge">3</span> 
							<span id="step3_cont"></span>
							<br>The OPD screen will start for you. To stop it either log off or press the blue logo and stop it there. 
							<button class="btn btn-sm btn-info" id="step3_OK" >Start calling patient</button>
						</div>
					</div>
					<div class="panel-footer" >
					</div>
				</div>
		</div>
	</div>
</div>
<script>
var seat_list = JSON.parse('<?php echo json_encode($seat_list); ?>');
//console.log(seat_list);
var room_id = null;
var doctor_id = parseInt('<?php echo $this->session->userdata('UID'); ?>');
var token = null;
$(function(){
	$("#step1").show();
	$("#step2").hide();
	$("#step3").hide();
	/*$("#step1_yes").click(
			function(){
				$("#step3").hide();
				$("#step2").hide();
				$("#step1").show();
			}
	);
	$("#step1_no").click(
			function(){
				self.document.location = "<?php //echo site_url("doctor/home_page"); ?>";
			}
	);*/
	$( "input[role='room_select']" ).click(function(){
			$("#step3").hide();
			$("#step2").show();
			room_id = $(this).attr("roomid");
			$("#step2_cont").html('');
			var html = '';
			if (!seat_list[room_id]){
				html += '<input type="button" class="btn btn-sm btn-default"  onclick="seat_new()" id="new_seat" role="seat_select" seatid="" value="Take a new seat" >&nbsp;';
				$("#step2_cont").html(html);
				return;
			}
			for (var i in seat_list[room_id]){
				html += '<input type="button" ';
				if (doctor_id == seat_list[room_id][i]["doctor_id"] ){
					html += ' class="btn btn-sm btn-danger"  value="SEAT '+seat_list[room_id][i]["doctor_number"]+' YOU" ';
					html += '  id="seat_'+seat_list[room_id][i]["opd_room_doctor_id"]+'" role="seat_select" seatid="'+seat_list[room_id][i]["opd_room_doctor_id"]+'"  >&nbsp;';
					//$("#step3_cont").html('You are assigned to room <span class="label label-danger" >'+room_id+'</span>, seat no.<span span class="label label-danger">'+response+'</span>');
					$("#step3").show();
					$("#step3_OK").click(
					function(){
							start_calling(room_id,doctor_id,"OPD");
					});
				}
				else{
					html += ' class="btn btn-sm btn-success" onclick="seat_select(this)"  value="SEAT '+seat_list[room_id][i]["doctor_number"]+' Select this" ';
					html += '  id="seat_'+seat_list[room_id][i]["opd_room_doctor_id"]+'" role="seat_select" seatid="'+seat_list[room_id][i]["opd_room_doctor_id"]+'"  >&nbsp;';
					html += '<input type="button" class="btn btn-sm btn-default"  onclick="seat_new()" id="new_seat" role="seat_select" seatid="" value="Take a new seat" >&nbsp;';
				}
				
			}
			
			$("#step2_cont").html(html);
	});

});

function seat_new(){
		var request = $.ajax({
			url: "<?php echo base_url(); ?>index.php/qdisplay/take_new_seat/"+room_id,
			type: "post"
		});
		request.done(function (response, textStatus, jqXHR){
			if (response>0){
				$("#step3_cont").html('You are assigned to room <span class="label label-danger" >'+room_id+'</span>, seat no.<span span class="label label-danger">'+response+'</span>');
				$("#step3").show();
				$("#step3_OK").click(
				function(){
						start_calling(room_id,doctor_id,"OPD");
				});
			}
			else{
				alert("Error selecting new seat");
			}
			
		});
}
function seat_select(btn){
		var seat_id = $(btn).attr("seatid");
                //alert(seat_id);
                //return false;
		var request = $.ajax({
			url: "<?php echo base_url(); ?>index.php/qdisplay/take_seat/"+seat_id+"/"+room_id,
			type: "post"
		});
		request.done(function (response, textStatus, jqXHR){
			if (response>0){
				$("#step3_cont").html('You are assigned to room <span class="label label-danger" >'+room_id+'</span>, seat no.<span span class="label label-danger">'+response+'</span>');
				$("#step3").show();
				$("#step3_OK").click(
				function(){
						start_calling(room_id,doctor_id,"OPD");
				});
			}
			else{
				alert("Error selecting seat");
			}
			
		});
}
</script>
