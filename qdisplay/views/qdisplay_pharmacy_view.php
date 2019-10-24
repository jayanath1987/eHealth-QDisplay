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
echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
echo "\n<html xmlns='http://www.w3.org/1999/xhtml'>";
echo "\n<head>";
echo "\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>";
echo "\n<title>QDisplay </title>";
echo "\n<link rel='icon' type='". base_url()."image/ico' href='images/mds-icon.png'>";
echo "\n<link rel='shortcut icon' href='". base_url()."images/mds-icon.png'>";
echo "\n<script type='text/javascript' src='". base_url()."js/jquery.js'></script>";
echo "\n<script type='text/javascript' src='".base_url()."js/bootstrap/js/bootstrap.min.js' ></script>";
echo "\n<script type='text/javascript' src='".base_url()."js/qdisplay/howler.js' ></script>";
echo "\n<script type='text/javascript' src='".base_url()."js/qdisplay/qdisplay.js' ></script>";
echo "\n<link href='". base_url()."js/bootstrap/css/bootstrap.min.css' rel='stylesheet' type='text/css' />";
echo "\n    <link href='". base_url()."css/qdisplay.css' rel='stylesheet' type='text/css' />";  

echo "\n</head>";
	
?>
<body style="background:darkblue;">
<div >
	<nav class="navbar navbar-default " role="navigation" id="nav">
		  <div class="container-fluid">
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			  <ul class="nav navbar-nav">
				<li class=""><a href="#"><b class="brand">QDisplay</b> <b><i>by <img src="../../images/icta.png" alt="ICTA" height="62" width="202"></i></b></a></li>
			  </ul>
			  <ul class="nav navbar-nav center">
				<li class="" ><a href="#"><b id="dte"></b></a></li>
			  </ul>
			  <ul class="nav navbar-nav pull-right">
				<li class=""><a href="#"><b ><?php echo $hospital_info["Name"]; ?></b></a></li>
			  </ul>
			</div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
	</nav>
	<div id="box_cont">
	</div>
</div>
</body>
<script>
<?php
	if (!isset($room_list)) $room_list = null;
?>

QDisplay.set_base_url("<?php echo base_url(); ?>");
QDisplay.set_ajax_url("<?php echo site_url("qdisplay/get_pharmacy_queue"); ?>");
QDisplay.ajax_qdata = JSON.parse('<?php echo json_encode($q_data); ?>');
QDisplay.ajax_config = JSON.parse('<?php echo json_encode($config); ?>');
QDisplay.hospital_info = JSON.parse('<?php echo json_encode($hospital_info); ?>');
QDisplay.load_audio_files(
		function(){
			QDisplay.run_pharmacy_display();
		}
	);

</script>
