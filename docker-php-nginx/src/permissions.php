<?php
	if (!$_SESSION["id"]){
		return [];
	}
	$db = new SQLite3('db/gsc-panel.db');
	$statement = $db->prepare('SELECT * FROM users WHERE id=?');
	$statement->bindValue(1, $_SESSION['id'], SQLITE3_TEXT);
	$result = $statement->execute();
	$row = $result->fetchArray(SQLITE3_ASSOC);
	$statement = $db->prepare('SELECT * FROM roles WHERE id=?');
	$statement->bindValue(1, $row['role'], SQLITE3_INTEGER);
	$result = $statement->execute();
	$row = $result->fetchArray(SQLITE3_ASSOC);
	return explode('|', $row['permissions']);
?>