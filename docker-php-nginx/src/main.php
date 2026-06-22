<?php
	session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<link rel="stylesheet" href="styles.css">
	<script src="common_funcs.js"></script>
    <title>GSC - Panel | Home</title>
</head>
<style>
	body {
		background-image: url('content/con_citizens_revolt.png');
		background-size: cover;
		overflow: hidden;
	}
</style>
<body>
	<div class='sidenav'>
		<a class='sidenav-element-choosed' href=''>
			<img src='content/home.svg' style='width: 50%; height: 50%;'>
			<div>Home</div>
		</a>
		<?php $perms = require 'permissions.php'; ?>
		<?php if (in_array('owner', $perms)) {?>
		<a class='sidenav-element' href='/admin'>
			<img src='content/admin.svg' style='width: 50%; heigh: 50%;'>
			<div>Admin</div>
		</a>
		<?php } ?>
		<a class='sidenav-element' href='leave' style='margin-top: auto;'>
			<img src='content/door.svg' style='width: 55%; height: 55%;'>
			<div>Log Out</div>
		</a>
	</div>
	<div class='main-grid'>
		<div>
			<h1 style='margin-bottom: -3%;' id='host_name'>My Garry's Mod Server</h1>
			<p style='margin-bottom: 2%;' id='players'>Players: 0/32</p>
			<table cellspacing=0 class='table_head'>
				<tr>
					<th class='filter' onclick='filter(0)'>Name <span style='font-size: 10pt;' id='gmod_nick'></span></th>
					<th class='filter' onclick='filter(1)'>Playtime <span style='font-size: 10pt;' id='gmod_time'></th>
					<th class='filter' onclick='filter(2)'>Ping <span style='font-size: 10pt;' id='gmod_ping'></th>
				</tr>
			</table>
			<table cellspacing=0 class='table_scroll custom-scroll' id='main_table'>
				
			</table>
		</div>
		<div class='global_seggins_block custom-scroll' id ='global-settings'>
			
		</div>
	</div>
	<div class='blur-background' onclick='ClosePlayerMenu(); CloseActionMenu()'>
		
	</div>
	<div class='player-menu'>
		<a class='nick_title' target="_blank"></a>
		<img class='avatar'>
		<table class='table_head table_head_menu' cellspacing=0>
			<th>Name</th>
			<th>Value</th>
		</table>
		<table class='table_scroll table_head_menu custom-scroll' id='player-info' cellspacing=0>
			
		</table>
		<div class='actions_container'>

		</div>
	</div>
	<div class='action-menu'>
		<h3 id='ActionTitle'></h3><br><br>
		<input type="text" class='action-text' id='enter_argument' autocomplete="off"><br>
		<select class='action-select' id='select_argument'>
		</select>
		<button class='action-submit' onclick='SendCommandToGmod()'>Submit</button>
	</div>
	<div class='notification' id='notification'>
		
	</div>
</body>
<script>
	var socket = new WebSocket(<?php echo '"ws://'.gethostbyname($_SERVER['HTTP_HOST']).':8080"'; ?>);
	var is_player_function = false
	var currPlayer;
	var currCommand;
	const players = [];
	const commands = [];
	function sendWSMsg(msg) {
		let msg_verify = {
			token: <?php echo '"'.$_SESSION['ws_token'].'"'; ?>,
			id: <?php echo $_SESSION['id']; ?>,
			msg: msg
		};
		socket.send(JSON.stringify(msg_verify));
	}
	let functions = {
		players: function(data){
			let players_text = document.getElementById('players');
			players_text.innerHTML = 'Players: ' + data;
		},
		host_name: function(data){
			let host_name = document.getElementById('host_name');
			host_name.innerHTML = data;
		},
		players_list: function(data){
			players.length = 0;
			data.forEach((player) => {
				let tbl = {
					gmod_nick: player.nick,
					steamid: player.steamid,
					gmod_ping: player.ping,
					gmod_time: player.playtime,
					userid: player.userid,
					gscinfo: player.gscinfo
				};
				players.push(tbl);
			});
			fill_main_table();
		},
		player_info: function(data) {
			const player_info = document.getElementById('player-info');
			data.forEach(function(v,k){
				player_info.innerHTML += '<tr><td>' + v[0] + '</td><td>' + v[1] + '</td>';
			});
		},
		commands_list: function(data) {
			commands.length = 0;
			data.forEach((command) =>{
				let tbl = {
					name: command.name,
					nicename: command.nicename,
					placeholder: command.placeholder,
					is_player_function: command.is_player_function,
					value: command.value
				};
				commands.push(tbl);
			});
			fill_commands();
		},
		get_command_placeholder: function(data) { //only for selectable
			const select_argument = document.getElementById('select_argument');
			for (i=0; i < data.options.length; i++) {
				select_argument.innerHTML += '<option>' + data.options[i] + '</option>';
			}
			select_argument.selectedIndex = data.options.indexOf(commands.find(command => command.name == data.command_name).value)
			if (select_argument.selectedIndex == -1) {
				select_argument.selectedIndex = 0;
			}
		},
	}
	socket.onmessage = function(event){
		let data = JSON.parse(event.data);
		if (functions[data.func]){
			functions[data.func](data.msg);	
		}
	}
	socket.onopen = function(){
		let msg = {
			type: "update_request"
		};
		sendWSMsg(msg);
	}
	
	
	function fill_main_table(){
		const main_table = document.getElementById('main_table');
		main_table.innerHTML = ''
		players.forEach(function(v,k){
			let gmod_time;
			if (v.gmod_time > 60) {
				gmod_time = Math.ceil(v.gmod_time / 60) + 'h'
			} 
			else {
				gmod_time = v.gmod_time + 'm';
			}
			main_table.innerHTML += '<tr><td><span class="pl_nick">' 
			+ v.gmod_nick + '</span><img src="content/pencil.svg" class="pencil" onclick="OpenPlayerMenu('+k+')"></td>' +
			'<td>' + gmod_time + '</td>' +
			'<td>' + v.gmod_ping + 'ms</td>' +
			'</tr>';
		})
	}
	
	<?php
		echo 'let allowed_commands = new Set();';
		foreach($perms as $v){
			echo 'allowed_commands.add("'.$v.'");';
		}
	?>
	
	function fill_commands(){
		const commands_container = document.getElementsByClassName('actions_container')[0];
		commands_container.innerHTML = '';
		commands.forEach(function(v,k){
			if (allowed_commands.has(v.name) && v.is_player_function) {
				commands_container.innerHTML += '<div class="action" onclick="OpenCommandPanel('+ k
					+')"><span class="action_text">' + v.nicename +'</span></div>';
			}
		});
		const global_settings = document.getElementById('global-settings');
		global_settings.innerHTML = '';
		commands.forEach(function(v,k){
			if (allowed_commands.has(v.name) && !v.is_player_function) {
				global_settings.innerHTML += 
					"<div class='global_seggins_element'>" +
						"<div class='global_seggins_text'>" +
							"<span class='global_seggins_text_caption'>" + v.nicename + 
							":&nbsp;</span>"  + v.value +
						"</div>" +
						"<img src='content/pencil.svg' class='global_seggins_pencil'" +
						"onclick='OpenCommandPanel(" + k + ")'>" +
					"</div>"
			}
		});
	}
	
	const filters = [
		'gmod_nick',
		'gmod_time',
		'gmod_ping'
	];
	function filter(filter_index){
		const filter_name = filters[filter_index]
		for (i=0; i < filters.length; i++){
			if (filters[i] == filter_name){
				continue;
			}
			document.getElementById(filters[i]).innerHTML = '';
		}
		const filter = document.getElementById(filter_name);
		if (filter.innerHTML == '') {
			filter.innerHTML = '▼'
		}
		else if (filter.innerHTML == '▲') {
			filter.innerHTML = '▼'
		} 
		else {
			filter.innerHTML = '▲'
		}
		if (filter.innerHTML == '▲'){
			players.sort(function(a,b){
				if (a[filter_name] > b[filter_name]) {
					return 1;
				}
				else {
					return -1;
				}
			})
		}
		else {
			players.sort(function(a,b){
				if (a[filter_name] < b[filter_name]) {
					return 1;
				}
				else {
					return -1;
				}
			})
		}
		fill_main_table();
	}
	
	function ClosePlayerMenu(){
		const background = document.getElementsByClassName('blur-background')[0];
		const player_menu = document.getElementsByClassName('player-menu')[0];
		background.style.display = 'none';
		player_menu.style.display = 'none';
		const textInput = document.getElementById('enter_argument');
		textInput.value = '';
	}
	function CloseActionMenu(){
		const background = document.getElementsByClassName('blur-background')[0];
		const action_menu = document.getElementsByClassName('action-menu')[0];
		background.style.display = 'none';
		action_menu.style.display = 'none';
		is_player_function = false;
		const textInput = document.getElementById('enter_argument');
		textInput.value = '';
	}
	
	function OpenPlayerMenu(id){
		const player = players[id];
		currPlayer = player;
		is_player_function = true;
		
		const background = document.getElementsByClassName('blur-background')[0];
		const player_menu = document.getElementsByClassName('player-menu')[0];
		background.style.display = 'block';
		player_menu.style.display = 'flex';
		
		const player_info = document.getElementById('player-info');
		player_info.innerHTML = `
			<tr>
				<td>SteamID64</td>
				<td id = 'SteamID64'></td>
			</tr>
			<tr>
				<td>Steam Nick</td>
				<td id = 'SteamNick'></td>
			</tr>
		`
		
		let msg = {
			type: "player_info_request",
			userId: player.userid,
		}
		sendWSMsg(msg);
		
		const nick_title = document.getElementsByClassName('nick_title')[0];
		nick_title.href = 'https://steamcommunity.com/profiles/' + player.steamid;
		
		fetch('/getsteamprofile?steamid='+player.steamid)
		.then(response => response.json())
		.then(json => {
			nick_title.innerHTML = player.gmod_nick;
			const avatar = document.getElementsByClassName('avatar')[0];
			avatar.src = json.avatar;
			const steamid64 = document.getElementById('SteamID64');
			steamid64.innerHTML = player.steamid;
			const steam_nick = document.getElementById('SteamNick');
			steam_nick.innerHTML = json.steam_nick;
		})
	}
	
	function OpenCommandPanel(id){
		ClosePlayerMenu();
		const background = document.getElementsByClassName('blur-background')[0];
		const command_menu = document.getElementsByClassName('action-menu')[0];
		background.style.display = 'block';
		command_menu.style.display = 'flex';
		
		const command = commands[id];
		currCommand = command
		
		const commandTitle = document.getElementById('ActionTitle');
		const commandTextInput = document.getElementById('enter_argument');
		commandTitle.innerHTML = command.nicename + ':';
		commandTextInput.placeholder = command.placeholder;
		
		const select_argument = document.getElementById('select_argument');
		const enter_argument = document.getElementById('enter_argument');
		if (command.placeholder == '') {
			enter_argument.style.display = 'none';
			select_argument.style.display = 'block';
			select_argument.innerHTML = '';
			let msg = {
				type: 'get_command_placeholder_request',
				command_name: command.name,
			}
			sendWSMsg(msg);
		} 
		else {
			enter_argument.style.display = 'block';
			select_argument.style.display = 'none';
		}
		
	}
	
	function SendCommandToGmod(){
		let userID = 0;
		if (is_player_function) {
			userID = currPlayer.userid;
		}
		
		const select_argument = document.getElementById('select_argument');
		const enter_argument = document.getElementById('enter_argument');
		let argument = '';
		if (enter_argument.style.display != 'none') {
			argument = enter_argument.value;
		}
		if (select_argument.style.display != 'none') {
			argument = select_argument.selectedIndex + 1
		}
		
		let msg = {
			type: "game_activity",
			command: currCommand.name,
			data: {
				argument: argument,
				userID: userID
			}
		};
		sendWSMsg(msg);
		CloseActionMenu();
	}
</script>
</html>