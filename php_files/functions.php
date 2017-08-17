<?php 

	function clearSessionBools(){
		unset($_SESSION['tmppostbool']);
		unset($_SESSION['tmppostemail']);
		unset($_SESSION['tmppostuserfname']);
		unset($_SESSION['tmppostuserlname']);
		unset($_SESSION['tmppostphone']);
	}


	
?>