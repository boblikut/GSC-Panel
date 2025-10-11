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
			$result = $db->query("SELECT * FROM roles WHERE name='".$row['role']."'");
			$row = $result->fetchArray(SQLITE3_ASSOC);
			$_SESSION['permissions'] = explode('|', $row['permissions']);
			$_SESSION['ws_token'] = bin2hex(random_bytes(15));
			$statement = $db->prepare('UPDATE users SET ws_token=? WHERE id=?');
			$statement->bindValue(1, $_SESSION['ws_token'], SQLITE3_TEXT);
			$statement->bindValue(2, $_SESSION['id'], SQLITE3_INTEGER);
			$statement->execute();
			header('Location: /');
			die;
		} else {
			echo '<h2 align="center" style="color: red;">INCORRECT PASSWORD OR LOGIN</h2>';
		}
	}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSC Panel</title>
</head>
<body>
	<h3 align='center'>Enter to the account:</h3>
	<form method='POST' action='login' align='center'>
		<input name = 'flogin' class="player-search rounded-border" style='margin-bottom: 10px;' type='text' placeholder='Enter the login...'><br>
		<input name = 'fpassword' class="player-search rounded-border" style='margin-bottom: 10px;' type='password' placeholder='Enter the password...'><br>
		<input type='submit' value='Submit✅' class='submit'>
	</form>
</body>
