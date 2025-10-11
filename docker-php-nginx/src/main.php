<?php
	session_start();
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
	<?php
		echo '<script>';
			echo 'let allowed_commands = new Set();';
			foreach($_SESSION['permissions'] as $v){
				echo 'allowed_commands.add("'.$v.'");';
			}
		echo '</script>';
	?>
	<script>
		var socket = new WebSocket(<?php echo '"ws://'.gethostbyname($_SERVER['HTTP_HOST']).':8080"'; ?>);
		let global_players;
		let global_player_commands;
		function updatePlayers(){
			let player_commands_html = "";
			global_player_commands.forEach((command) => {
				if (allowed_commands.has(command.name)) {
					player_commands_html += `
						<button onclick = "OpenChangeRequest('${command.name}', 'textarea', true, this, '${command.placeholder}')">
							${command.nicename}
						</button><br>
					`;
				}
			});
			let players_list = document.getElementById('players-list');
			let players_list_content = "";
			let player_search_value = document.getElementById('player-search').value;
			global_players.forEach((player) => {
				if (player_search_value && !player.nick.toLowerCase().includes(player_search_value.toLowerCase())){
					return;
				}
				players_list_content += `<div class = 'player'><a href='https://steamcommunity.com/profiles/${player.steamid}'>${player.nick}</a>
					<div class='dropdown'>
					  <button class='dropbtn' id='player'>🔧</button>
					  <div class='dropdown-content' data-user-id = '${player.userid}'>
							${player_commands_html}
					  </div>
					</div>
					</div>`;
			});
			players_list.innerHTML = players_list_content;
		}
		let functions = {
			players: function(data){
				let players_text = document.getElementById('players');
				players_text.innerHTML = 'Players: ' + data;
			},
			map_name: function(data){
				let map_name = document.getElementById('map_name');
				map_name.innerHTML = 'Map: ' + data;
			},
			gamemode_name: function(data){
				let gamemode_name = document.getElementById('gamemode_name');
				gamemode_name.innerHTML = 'Gamemode: ' + data;
			},
			host_name: function(data){
				let host_name = document.getElementById('host_name');
				host_name.innerHTML = data;
			},
			maps_list: function(data){
				let maps_list = document.getElementById('fullscreen-input-select');
				let maps_list_content = "";
				data.forEach((map_name) => {
					maps_list_content += "<option value = '"+map_name+"'>" + map_name + "</option>";
				});
				maps_list.innerHTML = maps_list_content;
			},
			gamemodes_list: function(data){
				let maps_list = document.getElementById('fullscreen-input-select');
				let maps_list_content = "";
				data.forEach((gamemode) => {
					maps_list_content += "<option value = '"+gamemode.name+"'>" + gamemode.title + "</option>";
				});
				maps_list.innerHTML = maps_list_content;
			},
			players_list: function(data){
				global_players = [];
				data.players.forEach((player) => {
					let tbl = {
						nick: player.nick,
						steamid: player.steamid,
						userid: player.userid
					};
					global_players.push(tbl);
				});
				global_player_commands = [];
				data.player_commands.forEach((command) =>{
					let tbl = {
						name: command.name,
						nicename: command.nicename,
						placeholder: command.placeholder
					};
					global_player_commands.push(tbl);
				})
				updatePlayers();
			}
		}
		socket.onmessage = function(event){
			let data = JSON.parse(event.data);
			functions[data.func](data.msg);
		}
		socket.onopen = function(){
			let msg = {
				type: "update_request"
			};
			let msg_verify = {
				token: <?php echo '"'.$_SESSION['ws_token'].'"'; ?>,
				id: <?php echo $_SESSION['id']; ?>,
				msg: msg
			};
			let json = JSON.stringify(msg_verify);
			socket.send(json);
		}
	</script>

	<script>
		let open_functions = {
			map: function(){
				let msg = {
					type: "maps_list_request"
				};
				let msg_verify = {
					token: <?php echo '"'.$_SESSION['ws_token'].'"'; ?>,
					id: <?php echo $_SESSION['id']; ?>,
					msg: msg
				};
				socket.send(JSON.stringify(msg_verify));
			},
			gamemode: function(){
				let msg = {
					type: "gamemodes_list_request"
				};
				let msg_verify = {
					token: <?php echo '"'.$_SESSION['ws_token'].'"'; ?>,
					id: <?php echo $_SESSION['id']; ?>,
					msg: msg
				};
				socket.send(JSON.stringify(msg_verify));
			},
		}

		function OpenChangeRequest(command, type, is_player_function, button, placeholder){
			const fullscreenContainer = document.getElementById('fullscreen-container-'+type);
			const openButton = document.getElementById(command) || button;
			const closeButton = document.getElementById('close-fullscreen-button-'+type);
			const sendButton = document.getElementById('send-fullscreen-button-'+type);
			const fullscreenInput = document.getElementById('fullscreen-input-'+type);

			openButton.onclick = () => {
				if (type === 'textarea') {
					fullscreenInput.placeholder = placeholder;
				}
				if (type === 'select') {
					open_functions[command]();
				}
				fullscreenContainer.classList.add('active');
				fullscreenInput.focus();
			};

			closeButton.onclick = () => {
				fullscreenContainer.classList.remove('active');
			};

			sendButton.onclick = () => {
				let userID = 0;
				if (is_player_function) {
					userID = button.parentNode.dataset.userId;
				}
				let msg = {
					type: "game_activity",
					command: command,
					data: {
						argument: fullscreenInput.value,
						userID: userID
					}
				};
				let msg_verify = {
					token: <?php echo '"'.$_SESSION['ws_token'].'"'; ?>,
					id: <?php echo $_SESSION['id']; ?>,
					msg: msg
				};
				socket.send(JSON.stringify(msg_verify));
				fullscreenContainer.classList.remove('active');
			};
		}
	</script>

	<div id="fullscreen-container-textarea">
	  <div id="input-wrapper">
		<textarea id="fullscreen-input-textarea" placeholder="Enter the text..."></textarea>
		<button id="close-fullscreen-button-textarea">Close</button>
		<button id="send-fullscreen-button-textarea" style = "float: right">Send</button>
	  </div>
	</div>

	<div id="fullscreen-container-select">
	  <div id="input-wrapper">
		<select id='fullscreen-input-select'></select>
		<button id="close-fullscreen-button-select">Close</button>
		<button id="send-fullscreen-button-select" style = "float: right">Send</button>
	  </div>
	</div>

	<h1 align = 'center' id = "host_name">My Garry's Mod Server</h1>
	<div class = "server-info">
		<div class = "rounded-border">
			<text id = "map_name">Map: gm_construct</text>
			<?php if (in_array('map', $_SESSION['permissions'])){?>
				<button id="map">🔧</button>
			<?php }?>
			<script>
				var button = document.getElementById("map");
				button.addEventListener("click", function() {
					OpenChangeRequest("map", "select");
				});
			</script>
		</div>
		<div class = "rounded-border players-count">
			<text id="players">Players: 0/0</text>
		</div>
		<div class = "rounded-border">
			<text id = "gamemode_name">Gamemode: Sandbox</text>
			<?php if (in_array('gamemode', $_SESSION['permissions'])){?>
				<button id="gamemode">🔧</button>
			<?php }?>
			<script>
				var button = document.getElementById("gamemode");
				button.addEventListener("click", function() {
					OpenChangeRequest("gamemode", "select");
				});
			</script>
		</div>
	</div>
	<br>
	<div class="server-info">
		<div class = "rounded-border players-list" id = "players-list">

		</div>
	</div>
	<div class="server-info">
		<input type="text" class="player-search rounded-border" placeholder="Enter player's nickname..." id="player-search">
	</div>
	<script>
		const player_search = document.getElementById('player-search');
		player_search.addEventListener('input', (event) => {
			updatePlayers();
		});
	</script>
</body>
</html>
