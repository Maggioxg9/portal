<?php
	session_start();
	
	$ch = curl_init("https://api.density.io/v1");
	$auth = "Authorization: Bearer " . $_SESSION['api_token'];
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		$auth
		));
	
	$response = curl_exec($ch);
	$array = json_decode($response);
	curl_close($ch);
	
	$_SESSION['currentcount'] = $array['current_count'];
	echo $_SESSION['currentcount'];
?>