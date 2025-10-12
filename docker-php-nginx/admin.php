<?php
	session_start();
	if (!in_array('owner', $_SESSION['permissions'])){
		header("Location: /");
		die;
	}
	$db = new SQLite3('db/gsc-panel.db');
	if ($_POST['generate_new_token']){
		$db->exec("DELETE FROM gmod_token");
		$db->exec("INSERT INTO gmod_token (token) values('".bin2hex(random_bytes(15))."')");
		header("Location: ".$_SERVER["REQUEST_URI"]);
		die;
	}
	$json = file_get_contents('php://input');
	$rights = json_decode($json, true)['rights'];
	if ($rights){
		$db->exec('DELETE FROM permissions WHERE id > 1');
		foreach($rights as $v){
			$statement = $db->prepare('INSERT INTO permissions (name, description) values(?, ?)');
			$statement->bindValue(1, $v['name'], SQLITE3_TEXT);
			$statement->bindValue(2, $v['description'], SQLITE3_TEXT);
			$statement->execute();
		}
		header("Location: ".$_SERVER["REQUEST_URI"]);
		die;
	}
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$login = $_POST['flogin'];
		$password = $_POST['fpassword'];
		$role = $_POST['frole'];
		$editing_acount_id = $_POST['fediting_acount_id'];
		$deleting_acount_id = $_POST['deleting_acount_id'];
		//Account things server processing
		if ($login || $password || $editing_acount_id || $deleting_acount_id) {
			//Account creating
			if ($login && $password && !$editing_acount_id && !$deleting_acount_id){
				$statement = $db->prepare('SELECT * FROM users WHERE login = ?');
				$statement->bindValue(1, $login, SQLITE3_TEXT);
				$result = $statement->execute();
				if ($result->fetchArray()[0] == null) {
					$statement = $db->prepare('INSERT INTO users (login, password, role) values(?, ?, ?)');
					$statement->bindValue(1, $login, SQLITE3_TEXT);
					$statement->bindValue(2, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
					$statement->bindValue(3, $role, SQLITE3_TEXT);
					$statement->execute();
					header("Location: ".$_SERVER["REQUEST_URI"]);
					die;
				} else {
					echo "<script>alert('Login already exists!')</script>";
				}
			}
			elseif (!$editing_acount_id && !$deleting_acount_id) {
				echo "<script>alert('You didn\'t wrote login or password!')</script>";
			}
			//Account deleting
			if ($deleting_acount_id) {
				$statement = $db->prepare('DELETE FROM users WHERE id = ?');
				$statement->bindValue(1, $deleting_acount_id, SQLITE3_INTEGER);
				$statement->execute();
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
			//Account editing
			elseif ($editing_acount_id){
				if ($login && $password){
					$statement = $db->prepare("UPDATE users SET login = ?, password = ?, role = ? WHERE id = ?");
					$statement->bindValue(1, $editing_acount_id, SQLITE3_INTEGER);
					$statement->bindValue(2, $login, SQLITE3_TEXT);
					$statement->bindValue(3, $role, SQLITE3_TEXT);
					$statement->bindValue(4, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);

					$statement->execute();
				}
				elseif ($login && !$password) {
					$statement = $db->prepare("UPDATE users SET login = ?, role = ? WHERE id = ?");
					$statement->bindValue(1, $login, SQLITE3_TEXT);
					$statement->bindValue(2, $role, SQLITE3_TEXT);
					$statement->bindValue(3, $editing_acount_id, SQLITE3_INTEGER);

					$res = $statement->execute();
				}
				elseif (!$login && $password) {
					$statement = $db->prepare("UPDATE users SET password = ?, role = ? WHERE id = ?");
					$statement->bindValue(1, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
					$statement->bindValue(2, $role, SQLITE3_TEXT);
					$statement->bindValue(3, $editing_acount_id, SQLITE3_INTEGER);

					$statement->execute();
				}
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
		}
		$role_name = $_POST['frole_name'];
		$permissions = $_POST['fpermissions'];
		$deleting_role_id = $_POST['deleting_role_id'];
		$editing_role_id = $_POST['fediting_role_id'];
		if ($role_name || $permissions || $deleting_role_id || $editing_role_id ){
			if ($role_name && !$deleting_role_id && !$editing_role_id) {
				$statement = $db->prepare("INSERT INTO roles (name, permissions) values(?, ?)");
				$statement->bindValue(1, $role_name, SQLITE3_TEXT);
				$str = '|';
				foreach($permissions as $v){
					$str .= $v.'|';
				}
				$statement->bindValue(2, $str, SQLITE3_TEXT);
				$statement->execute();
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
			elseif(!$deleting_role_id && !$editing_role_id){
				echo "<script>alert('No role name!')</script>";
			}
			if ($deleting_role_id) {
				$statement = $db->prepare("DELETE FROM roles WHERE id=?");
				$statement->bindValue(1, $deleting_role_id, SQLITE3_INTEGER);
				$statement->execute();
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
			if ($editing_role_id && $role_name) {
				$statement = $db->prepare("UPDATE roles SET name=?, permissions=? WHERE id=?");
				$statement->bindValue(1, $role_name, SQLITE3_TEXT);
				$str = '|';
				foreach($permissions as $v){
					$str .= $v.'|';
				}
				$statement->bindValue(2, $str, SQLITE3_TEXT);
				$statement->bindValue(3, $editing_role_id, SQLITE3_INTEGER);
				$statement->execute();
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			} elseif($editing_role_id) {
				$statement = $db->prepare("UPDATE roles SET permissions=? WHERE id=?");
				$str = '|';
				foreach($permissions as $v){
					$str .= $v.'|';
				}
				$statement->bindValue(1, $str, SQLITE3_TEXT);
				$statement->bindValue(2, $editing_role_id, SQLITE3_INTEGER);
				$statement->execute();
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="ru">
	<head>
		<link rel="stylesheet" href="style.css">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>GSC Panel (Admin)</title>
	</head>
	<body>
	<script>
		window.onload = function() {
			const deletingForms = document.querySelectorAll('[data-shouldAsk]');

			deletingForms.forEach(v => {
				v.onsubmit = function(){
					return confirm('Do you sure that want to delete this ' + v.dataset.shouldask + '?');
				}
			});

			const generateForm = document.getElementById("generate_form");

			generateForm.onsubmit = function(){
				return confirm('Do you sure that you want to generate new token for gmod? You will need replace old token on new on gmod side!!!');
			}
		}

		function showTab(id, clickedButton) {
			let sidenav = document.getElementById("sidenav");
			let sidenav_buttons = sidenav.children;
			for (let i = 0; i < sidenav_buttons.length; i++) {
				sidenav_buttons[i].classList.remove('active');
			}
			if (clickedButton) {
				clickedButton.classList.add('active');
			}
			document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
			document.getElementById(id).style.display = 'block';
		}

		function OpenAccountCreatingPanel() {
			const fullscreenContainer = document.getElementById('account_menu');

			fullscreenContainer.classList.add('active');
		}

		function CloseAccountCreatingPanel() {
			const fullscreenContainer = document.getElementById('account_menu');

			fullscreenContainer.classList.remove('active');
		}

		function OpenRoleCreatingPanel() {
			const fullscreenContainer = document.getElementById('role_menu');

			fullscreenContainer.classList.add('active');
		}

		function CloseRoleCreatingPanel() {
			const fullscreenContainer = document.getElementById('role_menu');

			fullscreenContainer.classList.remove('active');
		}

		function addEditingLogin(login, id, role) {
			const form = document.getElementById('account-form');
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'fediting_acount_id';
			input.value = id;
			input.id = 'editing_acount_id';
			form.appendChild(input);
			const login_input = document.getElementById('login-input');
			login_input.value = login;
			const role_select = document.getElementById('role-select');
			role_select.value = role;
		}

		function addEditingRole(role, id, permissions) {
			const form = document.getElementById('role-form');
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'fediting_role_id';
			input.value = id;
			input.id = 'editing_role_id';
			form.appendChild(input);
			const role_input = document.getElementById('role-input');
			role_input.value = role;

			const all_cbs = document.querySelectorAll('[id^="perm_"]');
			all_cbs.forEach(v =>{
				v.checked = false;
			});

			let data = permissions.split('|');
			data.forEach(v =>{
				var cb = document.getElementById('perm_' + v);
				if (cb) {
					cb.checked = true;
				}
			});
		}

		function removeHiddenLogin() {
			const input = document.getElementById('editing_acount_id');
			if (input) {
				input.remove();
			}
		}

		function removeHiddenRole() {
			const input = document.getElementById('editing_role_id');
			if (input) {
				input.remove();
			}
		}

		function UpdateRights(){
			var socket = new WebSocket(<?php echo '"ws://'.gethostbyname($_SERVER['HTTP_HOST']).':8080"'; ?>);

			socket.onmessage = function(event){
				fetch('admin', {
				  method: 'POST',
				  headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				  },
				  body: event.data
				});
				socket.close();
				alert("Rights are updating. If you don't see result reload page again");
				location.reload();
			};

			socket.onopen = function(){
				let msg = {
					type: "funcs_request"
				};
				let msg_verify = {
					token: <?php echo '"'.$_SESSION['ws_token'].'"'; ?>,
					id: <?php echo $_SESSION['id']; ?>,
					msg: msg
				};
				let json = JSON.stringify(msg_verify);
				socket.send(json);
			};
		}
	</script>
		<div class = "admin">
			<div class="sidenav" id="sidenav">
				<button class = "active" onclick="showTab('tab-users', this)">Users</button>
				<button onclick="showTab('tab-roles', this)">Roles</button>
				<button onclick="showTab('tab-gmod-token', this)">Gmod token</button>
			</div>
			<div class="tab-content" id = "tab-users">
				<?php
					$result = $db->query("SELECT * FROM users");
					$first_row = $result->fetchArray();
					if ($first_row[0] != null) {
				?>
				<table>
				<tr>
					<th>Login</th>
					<th>Role</th>
					<th>Delete</th>
					<th>Edit</th>
				</tr>
				<?php
					//first row processing
					echo "<tr>";
					echo '<td style = "width: 250px;">'.htmlspecialchars($first_row['login']).'</td><td style = "width: 250px;">'.htmlspecialchars($first_row['role']).'</td>';
					echo '<td><form style="margin: auto; margin-left: 15%;" action="admin" method="POST" data-shouldAsk="user"><input type = "hidden" name="deleting_acount_id" value='.$first_row['id'].'><input type="submit" class="submit" value="❌"></form></td>';
					echo '<td><button onclick = "removeHiddenLogin(); addEditingLogin(\''.htmlspecialchars($first_row['login']).'\', '.$first_row['id'].', \''.$first_row['role'].'\'); OpenAccountCreatingPanel();">🔧</button></td>';
					echo "</tr>";

					while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
						echo "<tr>";
						echo '<td style = "width: 250px;">'.htmlspecialchars($row['login']).'</td><td style = "width: 250px;">'.htmlspecialchars($row['role']).'</td>';
						echo '<td><form style="margin: auto; margin-left: 15%;" action="admin" method="POST" data-shouldAsk="user"><input type = "hidden" name="deleting_acount_id" value='.$row['id'].'><input type="submit" class="submit" value="❌"></form></td>';
						echo '<td><button onclick = "removeHiddenLogin(); addEditingLogin(\''.htmlspecialchars($row['login']).'\', '.$row['id'].', \''.htmlspecialchars($row['role']).'\'); OpenAccountCreatingPanel();">🔧</button></td>';
						echo "</tr>";
					}
				?>
				</table>
				<?php
					}
					else{
						echo '<p style="font-family: \'Trebuchet MS\'; font-size: 25px; color: white;">No users</p>';
					}
				?>
				<button onclick = "OpenAccountCreatingPanel(); removeHiddenLogin();" style = "position: absolute; right: 0; margin-top: 50px; font-family: 'Trebuchet MS'; font-size: 15px;">🔨Create account</button>
				<div id="account_menu">
				  <div id="input-wrapper">
					<form method="POST" action="admin" id='account-form'>
						<input type='text' id='login-input' placeholder='Enter the login' name = 'flogin'><br>
						<input type = 'text' placeholder='Eneter the password' name = 'fpassword'><br>
						<select name='frole' id='role-select'>
							<?php
								$result = $db->query("SELECT * FROM roles");
								while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
									echo '<option value="'.htmlspecialchars($row['name']).'">'.htmlspecialchars($row['name']).'</option>';
								}
							?>
						</select><br><br>
						<input type = 'submit' value = 'Submit' style="float: right;">
					</form>
					<button onclick = "CloseAccountCreatingPanel()" id="close-fullscreen-button-textarea" style = "position: absolute; margin-top: -15px;">Close</button>
				  </div>
				</div>
			</div>
			<div class="tab-content" id = "tab-roles" style="display: none;">
				<?php
					$result = $db->query("SELECT * FROM roles");
					$first_row = $result->fetchArray();
					if ($first_row[0] != null) {
				?>
				<table>
				<tr>
					<th>Role</th>
					<th>Delete</th>
					<th>Edit</th>
				</tr>
				<?php
					//first row processing
					echo "<tr>";
					echo '<td style = "width: 250px;">'.htmlspecialchars($first_row['name']).'</td>';
					echo '<td><form style="margin: auto; margin-left: 15%;" action="admin" method="POST" data-shouldAsk="role"><input type = "hidden" class="submit" name="deleting_role_id" value='.$first_row['id'].'><input type="submit" value="❌"></form></td>';
					echo '<td><button onclick = "removeHiddenRole(); addEditingRole(\''.htmlspecialchars($first_row['name']).'\', '.$first_row['id'].', \''.htmlspecialchars($first_row['permissions']).'\'); OpenRoleCreatingPanel();">🔧</button></td>';
					echo "</tr>";

					while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
						echo "<tr>";
						echo '<td style = "width: 250px;">'.htmlspecialchars($row['name']).'</td>';
						echo '<td><form style="margin: auto; margin-left: 15%;" action="admin" method="POST" data-shouldAsk="role"><input type = "hidden" class="submit" name="deleting_role_id" value='.$row['id'].'><input type="submit" value="❌"></form></td>';
						echo '<td><button onclick = "removeHiddenRole(); addEditingRole(\''.htmlspecialchars($row['name']).'\', '.$row['id'].', \''.htmlspecialchars($row['permissions']).'\'); OpenRoleCreatingPanel();">🔧</button></td>';
						echo "</tr>";
					}
				?>
				</table>
				<?php
					}
					else{
						echo '<p style="font-family: \'Trebuchet MS\'; font-size: 25px; color: white;">No roles</p>';
					}
				?>
				<button onclick = "OpenRoleCreatingPanel(); removeHiddenRole();" style = "position: absolute; right: 0; margin-top: 50px; font-family: 'Trebuchet MS'; font-size: 15px;">🔨Create role</button>
				<button onclick = "UpdateRights()" style = "position: absolute; right: 0; margin-top: 95px; font-family: 'Trebuchet MS'; font-size: 15px;">🔃Update Rights</button>
				<div id="role_menu">
				  <div id="input-wrapper">
					<form method="POST" action="admin" id='role-form'>
						<input type='text' id='role-input' placeholder='Enter name of the role' name = 'frole_name'><br>
						<?php
							$result = $db->query("SELECT * FROM permissions");
							while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
								echo '<input type="checkbox" name="fpermissions[]" value="'.htmlspecialchars($row['name']).'" id="perm_'.htmlspecialchars($row['name']).'">';
								echo '<label>'.htmlspecialchars($row['description']).'</label><br>';
							}
						?>
						<input type = 'submit' value = 'Submit' style="float: right;">
					</form>
					<button onclick = "CloseRoleCreatingPanel()" id="close-fullscreen-button-textarea" style = "position: absolute; margin-top: -15px;">Close</button>
				  </div>
				</div>
			</div>
			<div class="tab-content" id = "tab-gmod-token" style="display: none;">
				<h1 style = "margin-left: 500px; margin-bottom: 0px;">Gmod token:</h1><br>
				<input style = "margin-left: 500px; margin-bottom: 20px;" type="text" class="player-search rounded-border" value=<?php
					$result = $db->query("SELECT * FROM gmod_token");
					$row = $result->fetchArray();
					if ($row[0] != null){
						echo '"'.$row['token'].'"';
					} else {
						echo '"No token!"';
					}
				?>
				readonly><br>
				<form style="margin-left: 534px;" action="admin" method="POST" id='generate_form'>
					<input type = "hidden" name="generate_new_token" value='1'>
					<input type="submit" value="Generate new token🧮">
				</form>
			</div>
		</div>
	</body>
</html>
