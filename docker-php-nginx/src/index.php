<?php 
	session_start();
	if (!$_SESSION["id"]){
		ob_start();
		include('login.php');
		$content = ob_get_clean();
		echo $content;
		die;
	}
?>
<div class = "topnav">
	<a href="/">Home</a>
	<?php
		if (in_array('owner', $_SESSION['permissions'])){
	?>
	<a href="admin">Admin Panel</a>
	<?php }?>
	<a href="leave" style="float:right">Log out</a>
</div>
<?php
	
	$url = $_SERVER['REQUEST_URI'];
	
	if ($url == '/'){
		ob_start();
		include('main.php');
		$content = ob_get_clean();
		echo $content;
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
	
?>