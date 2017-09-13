<?php
	session_start();
	
	$ch = curl_init('https://api.getdor.com/v1/tokens');
	$cont = "Content-Type: application/json";
	
	$tok = array("refresh_token" => "30cYae0ATX7JqGBnEdwf9FNwCn6lOv");
	$auth = json_encode($tok);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		$cont
		));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	$array = json_decode($response, true);

	$_SESSION['apitoken'] = $array['data']['token'];
?>