<?php
	if (!$_SESSION['id']) {
		include('error.html');
		die;
	}
	
	if (!in_array('owner', require 'permissions.php')) {
		include('error.html');
		die;
	}
	
	header("Content-Type: application/json;");
	
	$db = new SQLite3('db/gsc-panel.db');
	$result = $db->query("SELECT * FROM gmod_token");
	$row = $result->fetchArray();
	$token = 'No token!';
	if ($row[0] != null){
		$token = $row['token'];
	}
	
	$json = [
		'token' => $token
	];
	echo json_encode($json);
?>