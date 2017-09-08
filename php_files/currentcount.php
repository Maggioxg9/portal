<?php
	session_start();
	
	$ch = curl_init('https://api.density.io/v1/spaces/');
	$auth = "Authorization: Bearer tok_CcUbJqjQgYboQfYM4DuVHHIjdHuHFtjxlqeb0u9ZxCv";
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		$auth
		));
	
	$response = curl_exec($ch);
	curl_close($ch);
	
	$array = json_decode($response);
	
	$_SESSION['currentcount'] = $array['count'];
	echo $_SESSION['currentcount'];
?>