<?php
	session_start();
	$db = new SQLite3('db/gsc-panel.db');
	if (!in_array('owner', require 'permissions.php')){
		header("Location: /");
		die;
	}
	if ($_POST['generate_new_token']){
		$db->exec("DELETE FROM gmod_token");
		$db->exec("INSERT INTO gmod_token (token) values('".bin2hex(random_bytes(15))."')");
		die;
	}
	function GetRoleId($role_name){
		global $db; 
		$statement = $db->prepare('SELECT id FROM roles WHERE name=?');
		$statement->bindValue(1, $role_name, SQLITE3_TEXT);
		$result = $statement->execute();
		return $result->fetchArray(SQLITE3_ASSOC)['id'];
	}
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$login = $_POST['flogin'];
		$password = $_POST['fpassword'];
		$role = $_POST['frole'];
		$editing_acount_id = $_POST['fediting_acount_id'];
		$account_action = $_POST['account_action'];
		//Account things server processing
		if ($login || $password || $role || $editing_acount_id) {
			//Account creating
			if ($login && $password && !$editing_acount_id){
				$statement = $db->prepare('SELECT * FROM users WHERE login = ?');
				$statement->bindValue(1, $login, SQLITE3_TEXT);
				$result = $statement->execute();
				if ($result->fetchArray()[0] == null) {
					$statement = $db->prepare('INSERT INTO users (login, password, role, ws_token) values(?, ?, ?, ?)');
					$statement->bindValue(1, $login, SQLITE3_TEXT);
					$statement->bindValue(2, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
					$statement->bindValue(3, GetRoleId($role), SQLITE3_INTEGER);
					$statement->bindValue(4, bin2hex(random_bytes(15)), SQLITE3_TEXT);
					$statement->execute();
					$_SESSION['message'] = 'Success!';
					header("Location: ".$_SERVER["REQUEST_URI"]);
					die;
				} else {
					$_SESSION['message'] = 'Login already exists!';
					header("Location: ".$_SERVER["REQUEST_URI"]);
					die;
				}
			}
			elseif (!$editing_acount_id) {
				$_SESSION['message'] = 'You didn\\\'t wrote login or password!';
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
			//Account deleting
			if ($editing_acount_id && $account_action == 'Delete') {
				$statement = $db->prepare('DELETE FROM users WHERE id = ?');
				$statement->bindValue(1, $editing_acount_id, SQLITE3_INTEGER);
				$statement->execute();
				$_SESSION['message'] = 'Success!';
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
			//Account editing
			elseif ($editing_acount_id){
				if ($login && $password){
					$statement = $db->prepare("UPDATE users SET login = ?, password = ?, role = ?, ws_token = ? WHERE id = ?");
					$statement->bindValue(1, $login, SQLITE3_TEXT);
					$statement->bindValue(2, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
					$statement->bindValue(3, GetRoleId($role), SQLITE3_TEXT);
					$statement->bindValue(4, bin2hex(random_bytes(15)), SQLITE3_TEXT);
					$statement->bindValue(5, $editing_acount_id, SQLITE3_INTEGER);
					
					$statement->execute();
				}
				elseif ($login && !$password) {
					$statement = $db->prepare("UPDATE users SET login = ?, role = ? WHERE id = ?");
					$statement->bindValue(1, $login, SQLITE3_TEXT);
					$statement->bindValue(2, GetRoleId($role), SQLITE3_TEXT);
					$statement->bindValue(3, $editing_acount_id, SQLITE3_INTEGER);

					$res = $statement->execute();
				}
				elseif (!$login && $password) {
					$statement = $db->prepare("UPDATE users SET password = ?, role = ?, ws_token = ? WHERE id = ?");
					$statement->bindValue(1, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
					$statement->bindValue(2, GetRoleId($role), SQLITE3_TEXT);
					$statement->bindValue(3, bin2hex(random_bytes(15)), SQLITE3_TEXT);
					$statement->bindValue(4, $editing_acount_id, SQLITE3_INTEGER);

					$statement->execute();
				}
				$_SESSION['message'] = 'Success!';
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
		}
		$role_name = $_POST['frole_name'];
		$permissions = $_POST['fpermissions'];
		$editing_role_id = $_POST['fediting_role_id'];
		$role_action = $_POST['role_action'];
		if ($role_name || $permissions || $editing_role_id ){
			if ($role_name && !$editing_role_id) {
				$statement = $db->prepare("INSERT INTO roles (name, permissions) values(?, ?)");
				$statement->bindValue(1, $role_name, SQLITE3_TEXT);
				$str = '|';
				foreach($permissions as $v){
					$str .= $v.'|';
				}
				$statement->bindValue(2, $str, SQLITE3_TEXT);
				$statement->execute();
				$_SESSION['message'] = 'Success!';
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
			elseif(!$editing_role_id){
				$_SESSION['message'] = 'No role name!';
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
			if ($editing_role_id && $role_action == 'Delete') {
				$statement = $db->prepare('SELECT 1 FROM users WHERE role=?');
				$statement->bindValue(1, $editing_role_id, SQLITE3_INTEGER);
				$result = $statement->execute();
				$row = $result->fetchArray();
				if ($row[0] != null){
					$_SESSION['message'] = 'Cannot delete role that is using some user!';
					header("Location: ".$_SERVER["REQUEST_URI"]);
					die;
				}
				$statement = $db->prepare('DELETE FROM roles WHERE id=?');
				$statement->bindValue(1, $editing_role_id, SQLITE3_INTEGER);
				$statement->execute();
				$_SESSION['message'] = 'Success!';
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
				$_SESSION['message'] = 'Success!';
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
				$_SESSION['message'] = 'Success!';
				header("Location: ".$_SERVER["REQUEST_URI"]);
				die;
			}
		}
		else {
			$_SESSION['message'] = 'Error!';
			header("Location: ".$_SERVER["REQUEST_URI"]);
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
    <title>GSC - Panel | Admin</title>
</head>
<style>
	body {
		background-image: url('content/teops_g_planet.png');
		background-size: cover;
	}
</style>
<body>
	<div class='sidenav'>
		<a class='sidenav-element' href='/'>
			<img src='content/home.svg' style='width: 50%; height: 50%;'>
			<div>Home</div>
		</a>
		<a class='sidenav-element-choosed' href=''>
			<img src='content/admin.svg' style='width: 50%; height: 50%;'>
			<div>Admin</div>
		</a>
		<a class='sidenav-element' href='leave' style='margin-top: auto;'>
			<img src='content/door.svg' style='width: 55%; height=55%;'>
			<div>Log Out</div>
		</a>
	</div>
	<div class='sidenav-admin'>
		<a class='sidenav-element-choosed' style='cursor: pointer;' onclick='onTabChose(this, "1")'>
			<img src='content/users.svg' style='width: 50%; height=50%;'>
			<div>Users</div>
		</a>
		<a class='sidenav-element' style='cursor: pointer;' onclick='onTabChose(this, "2")'>
			<img src='content/roles.svg' style='width: 50%; height=50%;'>
			<div>Roles</div>
		</a>
		<a class='sidenav-element' style='cursor: pointer;' onclick='onTabChose(this, "3")'>
			<img src='content/token.svg' style='width: 50%; height=50%;'>
			<div>Token</div>
		</a>
	</div>
	<div class='main-settings' id='1'>
		<h3>Users</h3>
		<table cellspacing=0 class='table_head'  style='width: 22vw;'>
			<tr>
				<th>Login</th>
				<th>Role</th>
			</tr>
		</table>
		<table cellspacing=0 class='table_scroll custom-scroll' style='width: 22vw; height: 40vh;'>
			<?php 
			$result = $db->query("SELECT u.id AS user_id, u.login, r.name FROM users as u LEFT JOIN roles as r ON u.role=r.id");
			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				echo "<tr>";
				echo '<td><span class="pl_nick">'.htmlspecialchars($row['login']).'</span><img src="content/pencil.svg" class="pencil" 
					onclick="OpenUserMenu('.htmlspecialchars($row['user_id']).', \''.htmlspecialchars($row['login']).'\', \''.htmlspecialchars($row['name']).'\')"></td>';
				echo '<td>'.htmlspecialchars($row['name']).'</td>';
				echo "</tr>";
			}
			?>
		</table>
		<button class='create_user' onclick='OpenUserMenu()'>Create User</button>
		<div class='user-menu'>
			<h3 id='UserMenuCaption' style='margin-bottom: 5%;'>User Creating</h3>
			<form method="POST" action="admin" class='create_form'>
				<input type='text' placeholder='Enter the login...' name = 'flogin' id='login'><br>
				<input type = 'password' placeholder='Enter the password...' name = 'fpassword'><br>
				<input type = 'hidden' name='fediting_acount_id' id='editing_acount_id'>
				<select name='frole' style='margin:0; height: 5vh;' id='role'>
					<?php
						$result = $db->query("SELECT * FROM roles");
						while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
							echo '<option value="'.htmlspecialchars($row['name']).'">'.htmlspecialchars($row['name']).'</option>';
						}
					?>
				</select><br>
				<div style='display: flex; width: 50%; justify-content: center; gap: 5%;'>
					<input type = 'submit' name='account_action' value = 'Submit' style="width: 45%; text-align: center;">
					<input type = 'submit' name='account_action' value = 'Delete' style="width: 45%; text-align: center;" id='delete_account'>
				</div>
			</form>
		</div>
	</div>
	<div class='main-settings' id='2' style='display: none;'>
		<h3>Roles</h3>
		<table cellspacing=0 class='table_head'  style='width: 15vw;'>
			<tr>
				<th>Name</th>
			</tr>
		</table>
		<table cellspacing=0 class='table_scroll custom-scroll' style='width: 15vw; height: 40vh;'>
			<?php
				$result = $db->query("SELECT * FROM roles");
				while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
					echo '<tr>';
					echo '<td><span class="pl_nick">'.htmlspecialchars($row['name']).'</span><img src="content/pencil.svg" class="pencil" onclick="OpenRoleMenu('.$row['id'].', \''.htmlspecialchars($row['name']).'\', \''.htmlspecialchars($row['permissions']).'\')"></td>';
					echo '</tr>';
				}
			?>
		</table>
		<button class='create_role' onclick='OpenRoleMenu()'>Create Role</button>
		<button class='create_role' onclick='SyncRoles()'>Sync Rights</button>
		<div class="role-menu user-menu" style='height: 55%;'>
			<h3 id="RoleMenuCaption" style="margin-bottom: 5%;">Role Creating</h3>
			<form method="POST" action="admin" class="create_form">
				<input type="text" placeholder="Enter the role name..." name="frole_name" id="role_name"><br>
				<fieldset class="checkbox_block">
				<legend style="font-weight: bold; margin-bottom: 5px; font-size: 15pt; letter-spacing: 0.075em;">Permissions:</legend>
				<?php
					$result = $db->query("SELECT * FROM permissions");
					while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
						echo '<input type="checkbox" name="fpermissions[]" value="'.htmlspecialchars($row['name']).'">';
						echo '<label>'.htmlspecialchars($row['description']).'</label><br>';
					}
				?>
				</fieldset>
				<input type="hidden" name="fediting_role_id" id="editing_role_id">
				<div style="display: flex; width: 50%; justify-content: center; gap: 5%;">
				  <input type="submit" name="role_action" value="Submit" style="width: 45%; text-align: center;">
				  <input type="submit" name="role_action" value="Delete" style="width: 45%; text-align: center;" id="delete_role">
				</div>
		  </form>
		</div>
	</div>
	<div class='main-settings' id='3' style='display: none;'>
		<h3 align='center'>Token</h3>
		<div style=' display: block; width: 35%; margin-left: auto; margin-right: auto;'>
			<div class='token'>
				<input type="password" class='action-text' id='token_input' readonly
					value = <?php
						$result = $db->query("SELECT * FROM gmod_token");
						$row = $result->fetchArray();
						if ($row[0] != null){
							echo '"'.$row['token'].'"';
						} else {
							echo '"No token!"';
						}
					?>>
				<img src='content/copy.svg' class='copy' onclick='CopyToken()'>
				<img src='content/show.svg' class='copy' onclick='ToggleTokenVisibility()' id='show'>
			</div><br>
			<button style=' display: block; margin-left: auto; margin-right: auto; width: 17.5%; text-align: center;' onclick='GenerateNewToken()'>Generate</button>
		</div>
	</div>
	<div class='notification' id='notification'>
		
	</div>
	<div class='blur-background' onclick='CloseMenus()'>
		
	</div>
</body>
<script>
	function ToggleTokenVisibility() {
		const input = document.getElementById('token_input');
		const icon = document.getElementById('show');
		if (input.type === 'password') {
			input.type = 'text';
			icon.src = 'content/hide.svg';
		} else {
			input.type = 'password';
			icon.src = 'content/show.svg';
		}
	}
	function onTabChose(btn, id){
		const tabs = document.querySelectorAll('.main-settings');
		for (let tab of tabs) {
			if (tab.id == id) {
				tab.style.display = 'block';
			} 
			else {
				tab.style.display = 'none';
			}
		}
		const tab_buttons = document.querySelectorAll('.sidenav-admin')[0];
		for (let tab_button of tab_buttons.children) {
			tab_button.className = 'sidenav-element'; 
		}
		btn.className = 'sidenav-element-choosed';
	}
	function CopyToken(){
		const token_input = document.getElementById('token_input');
		notice('Token has copied!', 2000);
		navigator.clipboard.writeText(token_input.value);
	}
	
	function GenerateNewToken() {
		const token_input = document.getElementById('token_input');
		const formData = new FormData();
		formData.append('generate_new_token', 1);
		fetch('/admin', {
			method: 'POST',
			body: formData
		}).then(
			function() {
				fetch('/getgmodtoken', {
				  method: 'GET',
				})
				.then(response => response.json())
				.then(data => token_input.value = data.token)
				.then(notice('New token has generated!', 2000));
			}
		)
	}
	
	function CloseMenus(){
		const background = document.getElementsByClassName('blur-background')[0];
		const user_menu = document.getElementsByClassName('user-menu')[0];
		const role_menu = document.getElementsByClassName('role-menu')[0];
		background.style.display = 'none';
		user_menu.style.display = 'none';
		role_menu.style.display = 'none';
	}
	function OpenUserMenu(id, login, role){
		const background = document.getElementsByClassName('blur-background')[0];
		const user_menu = document.getElementsByClassName('user-menu')[0];
		background.style.display = 'block';
		user_menu.style.display = 'flex';
		const menu_caption = document.getElementById('UserMenuCaption');
		menu_caption.innerHTML = 'User Creating';
		const edit_element = document.getElementById('editing_acount_id');
		edit_element.value = null;
		const delete_account = document.getElementById('delete_account');
		delete_account.style.display = 'none';
		if (id) {
			menu_caption.innerHTML = 'User Editing';
			const login_element = document.getElementById('login');
			const role_element = document.getElementById('role');
			login_element.value = login;
			const targetOption = Array.from(role_element.options).find(v => v.text === role);
			targetOption.selected = true;
			const edit_element = document.getElementById('editing_acount_id');
			edit_element.value = id;
			delete_account.style.display = 'block';
		}
	}
	function OpenRoleMenu(id, roleName, permissions) {
		const background = document.getElementsByClassName('blur-background')[0];
		const role_menu = document.getElementsByClassName('role-menu')[0];
		background.style.display = 'block';
		role_menu.style.display = 'flex';
		const menu_caption = document.getElementById('RoleMenuCaption');
		const role_name_input = document.getElementById('role_name');
		const edit_element = document.getElementById('editing_role_id');
		const delete_button = document.getElementById('delete_role');
		menu_caption.innerHTML = 'Role Creating';
		role_name_input.value = '';
		edit_element.value = '';
		delete_button.style.display = 'none';
		const checkboxes = document.querySelectorAll('input[name="fpermissions[]"]');
		checkboxes.forEach(cb => cb.checked = false);
		if (id) {
			menu_caption.innerHTML = 'Role Editing';
			role_name_input.value = roleName;
			edit_element.value = id;
			delete_button.style.display = 'block';
			const permsArray = permissions.split('|');
			checkboxes.forEach(cb => {
				if (permsArray.includes(cb.value)) {
					cb.checked = true;
				}
			});
		}
	}
	function SyncRoles() {
		var socket = new WebSocket(<?php echo '"ws://'.gethostbyname($_SERVER['HTTP_HOST']).':8080"'; ?>);

		socket.onopen = function(){
			let msg = {
				type: "rights_request"
			};
			let msg_verify = {
				token: <?php echo '"'.$_SESSION['ws_token'].'"'; ?>,
				id: <?php echo $_SESSION['id']; ?>,
				msg: msg
			};
			let json = JSON.stringify(msg_verify);
			socket.send(json);
		};
		
		socket.onmessage = function(event){
			let data = JSON.parse(event.data);
			if (data.rights_updated == 1){
				notice('Rights have synced! Reload the page', 2000, document.getElementById("notification"));
			}
		};
	}
</script>
</html>