<?php
	function start_notice(){
		if ($_SESSION["message"]){
			echo '<script>notice(\''.$_SESSION["message"].'\', 2000, document.getElementById("notification"))</script>';
			unset($_SESSION["message"]);
		}
	}
 
	session_start();
	if (!$_SESSION["id"]){
		ob_start();
		include('login.php');
		$content = ob_get_clean();
		echo $content;
		start_notice();
		die;
	}
	
	$url = $_SERVER['REQUEST_URI'];
	
	if ($url == '/'){
		ob_start();
		include('main.php');
		$content = ob_get_clean();
		echo $content;
		start_notice();
		die;
	}
	
	preg_match('/\/(\w+)?|$/', $url, $match);
	$url = $match[1];
	
	$path = "$url.php";
	
	if (!file_exists($path)){
		include('notfound.html');
		die;
	}
	
	ob_start();
	include($path);
	$content = ob_get_clean();
	echo $content;
	start_notice();
?>