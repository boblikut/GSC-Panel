<?php
	if (!$_SESSION['id']) {
		include('error.html');
		die;
	}
	
	if (!$_GET['steamid']){
		include('error.html');
		die;
	}

	header("Content-Type: application/xml; charset=UTF-8");

	$result = file_get_contents('https://steamcommunity.com/profiles/'.$_GET['steamid'].'?xml=1');

	$profile = new SimpleXMLElement($result);
	$json = [
		'avatar' => (string)$profile->avatarFull,
		'steam_nick' => (string)$profile->steamID
	];
	echo json_encode($json);

?>