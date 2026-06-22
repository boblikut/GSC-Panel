require("gwsockets")

local ws_addres = "ws://localhost:8080"

local socket = GWSockets.createWebSocket(ws_addres)

---------------------------------------
--				globals				 --
---------------------------------------

local function writeTable(tbl)
	local tbl = {
		msg = tbl,
		id = "gmod",
		token = string.Trim(file.Read("gsc/gsc-token.txt", "DATA")) or ""
	}
	socket:write(util.TableToJSON(tbl))
end

function gsc.getPlayers()
	local players = {}
	local gscinfo = gsc.GetGSCInfo()
	
	for k, v in player.Iterator() do
		players[#players + 1] = {
			nick = v:Nick(),
			steamid = v:SteamID64(),
			ping = v:Ping(),
			playtime = math.max(1, math.ceil(v:TimeConnected() / 60)),
			userid = v:UserID(),
			gscinfo = {}
		}
	end
	
	local tbl = {
		func = "players_list",
		msg = players
	}
	
	writeTable(tbl)
end

function gsc.getPlayerInfo(userId)
	local info = {}
	local ply = Player(userId)
	local gscinfo = gsc.GetGSCInfo()
	
	if ply:IsValid() then
		for nicename, getinfofunc in pairs(gscinfo) do
			info[#info + 1] = {nicename, getinfofunc(ply)}
		end
	end
	
	local tbl = {
		func = "player_info",
		msg = info
	}
	
	writeTable(tbl)
end

function gsc.updatePlayers()
	local tbl = {
		func = "players",
		msg = player.GetCount().."/"..game.MaxPlayers()
	}
	writeTable(tbl)
	gsc.getPlayers()
end

function gsc.updateCommands()
	local commands = {}
	
	for k, v in pairs(gsc.commands) do
		commands[#commands + 1] = {
			name = k,
			nicename = v.nicename,
			placeholder = v.placeholder,
			is_player_function = v.is_player_func
		}
		if type(v.placeholder) == 'string' then
			commands[#commands].placeholder = v.placeholder
		else	
			commands[#commands].placeholder = '';
		end
		if v.getvaluefunc then
			commands[#commands].value = v.getvaluefunc()
		end
	end
	
	local tbl = {
		func = 'commands_list',
		msg = commands
	}
	
	writeTable(tbl)
end

function gsc.getCommandPlaceholder(command_name)
	local command = gsc.commands[command_name]
	
	if command then
		if type(command.placeholder) == 'function' then
			options = command.placeholder()
		elseif type(command.placeholder) == 'table' then
			options = command.placeholder
		end
	end
	
	local tbl = {
		func = 'get_command_placeholder',
		msg = {
			options = options,
			command_name = command_name
		}
	}
	
	writeTable(tbl)
end

function gsc.updateServerName()
	local tbl = {
		func = "host_name",
		msg = GetHostName()
	}
	writeTable(tbl)
end

---------------------------------------
--			Sockets work			 --
---------------------------------------

local onMessageFuncs = {
	game_activity = function(tbl)
		gsc.CallCommand(tbl.command, tbl.data.argument, tbl.data.userID)
	end,
	update_request = function()
		gsc.updatePlayers()
		gsc.updateCommands()
		gsc.updateServerName()
	end,
	player_info_request = function(tbl)
		gsc.getPlayerInfo(tbl.userId)
	end,
	get_command_placeholder_request = function(tbl)
		gsc.getCommandPlaceholder(tbl.command_name)
	end,
	rights_request = function()
		local rights = {}
		for k,v in pairs(gsc.commands) do
			local tbl = {}
			tbl.name = k
			tbl.description = v.description
			rights[#rights + 1] = tbl
		end
		
		local tbl = {
			rights = rights,
			id = "gmod",
			token = string.Trim(file.Read("gsc/gsc-token.txt", "DATA")) or ""
		}
		socket:write(util.TableToJSON(tbl))
		
	end
}

function socket:onMessage(json)
    local tbl = util.JSONToTable(json)
	if !tbl then
		print("JSON read was failed!")
		return
	end
	if !tbl.type or !onMessageFuncs[tbl.type] then return end
	onMessageFuncs[tbl.type](tbl)
end

function socket:onError(txt)
    print("Error: ", txt)
end

function socket:onConnected()
    print("Connected to GSC Panel WS server")
	onMessageFuncs.update_request()
end

function socket:onDisconnected()
    print("WebSocket disconnected")
	print("Socket Reconnecting activated...")

	timer.Create("Socket Reconnect", 1, 0, function()
		if socket:isConnected() then
			timer.Remove("Socket Reconnect")
			return
		end
		pcall(function() --Don't kick me by legs. Smth problem on GWSockets ig
			socket:open()
		end)
	end)
end

local filePath = "gsc/gsc-token.txt"

if !file.Exists(filePath, "DATA") then
    if !file.IsDir("gsc", "DATA") then
        file.CreateDir("gsc")
    end
    file.Write(filePath, "INSERT THE TOKEN")
end

socket:open()

timer.Simple(3, function()
	if !socket:isConnected() then
		print("Connection failed! Socket Reconnecting activated...")
		timer.Create("Socket Reconnect", 1, 0, function()
			if socket:isConnected() then
				timer.Remove("Socket Reconnect")
				return
			end
			pcall(function() --Don't kick me by legs. Smth problem on GWSockets side ig
				socket:open()
			end)
		end)
	end
end)

---------------------------------------
--		Hooks to update panel data	 --
---------------------------------------

--control amount of players at panel
hook.Add("PlayerInitialSpawn", "gsc-players-update", gsc.updatePlayers)
hook.Add( "PlayerDisconnected", "gsc-players-update", function()
	timer.Create("GSC players update", 0.1, 1, gsc.updatePlayers)
end)
