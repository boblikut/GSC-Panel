<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$login = $_POST['flogin'];
		$password = $_POST['fpassword'];
		$db = new SQLite3('db/gsc-panel.db');
		$statement = $db->prepare('SELECT * FROM users WHERE login=?');
		$statement->bindValue(1, $login, SQLITE3_TEXT);
		$result = $statement->execute();
		$row = $result->fetchArray(SQLITE3_ASSOC);
		if ($row != null && password_verify($password, $row['password'])){
			session_start();
			$_SESSION['id'] = $row['id'];
			$_SESSION['ws_token'] = bin2hex(random_bytes(15));
			$statement = $db->prepare('UPDATE users SET ws_token=? WHERE id=?');
			$statement->bindValue(1, $_SESSION['ws_token'], SQLITE3_TEXT);
			$statement->bindValue(2, $_SESSION['id'], SQLITE3_INTEGER);
			$statement->execute();
			$_SESSION['message'] = 'Welcome!';
			header('Location: /');
			die;
		} else {
			$_SESSION['message'] = 'Incorrect login or password!';
			header('Location: /');
			die;
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<link rel="stylesheet" href="styles.css">
	<script src="common_funcs.js"></script>
    <title>GSC - Panel | Login</title>
</head>
<style>
	body {
		background-image: url('content/teops_g_planet.png');
		background-size: cover;
	}
</style>
<body>
	<div class='sidenav'>
		<a class='sidenav-element' href=''>
			<img src='content/home.svg' style='width: 50%; height: 50%;'>
			<div>Home</div>
		</a>
	</div>
	<div class='main'>
		<h1 style='margin-bottom: 1%;'>Enter to the Account</h1>
		<form method='POST' action='login'>
			<input type='text' placeholder='Enter the login...' name='flogin' class='account-enter' style='margin-bottom: 1%; background-image: url("content/login.png"); background-size: 90% 120%; background-position: 125% 0%;' autocomplete="off"><br>
			<input type='password' placeholder='Enter the password...' name='fpassword' class='account-enter' style='margin-bottom: 1%; background-image: url("content/password.png"); background-size: 90% 110%; background-position: 100% 0%;' autocomplete="off"><br>
			<input type='submit' value='✓ Submit' class='account-enter-submit'>
		</form>
	</div>
	<div class='notification' id='notification'>
		
	</div>
</body>
</html>