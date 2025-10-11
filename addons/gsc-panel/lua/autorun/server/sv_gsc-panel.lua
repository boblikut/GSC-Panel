require("gwsockets")

local token = "2c8aa566e251602858e26424ba6b1c"
local ws_addres = "ws://localhost:8080"

local socket = GWSockets.createWebSocket(ws_addres)

---------------------------------------
--				globals				 --
---------------------------------------

local function writeTable(tbl)
	local tbl = {
		msg = tbl,
		id = "gmod",
		token = token
	}
	socket:write(util.TableToJSON(tbl))
end

function gsc.getPlayers()
	local players = {}
	
	for k, v in player.Iterator() do
		players[#players + 1] = {
			nick = v:Nick(),
			steamid = v:SteamID64(),
			userid = v:UserID()
		}
	end
	
	local player_commands = {}
	
	for k, v in pairs(gsc.commands) do
		if !v.is_player_command then continue end
		player_commands[#player_commands + 1] = {
			name = k,
			nicename = v.nicename,
			placeholder = v.placeholder
		}
	end
	
	local tbl = {
		func = "players_list",
		msg = {
			players = players,
			player_commands = player_commands
		}
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

function gsc.updateMap()
	local tbl = {
		func = "map_name",
		msg = game.GetMap()
	}
	writeTable(tbl)
end

function gsc.updateGamemode()
	local gamemode = engine.ActiveGamemode()
	local nice_gamemode
	for k, v in ipairs(engine.GetGamemodes()) do
		if v.name == gamemode then 
			nice_gamemode = v.title
			break
		end
	end
	local tbl = {
		func = "gamemode_name",
		msg = nice_gamemode
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

function gsc.getMaps()
	local maps = {}
	local files = file.Find("maps/*.bsp", "GAME")

    for _, filename in ipairs(files) do
        local mapName = string.gsub(filename, "%.bsp$", "")
        maps[#maps + 1] = mapName
    end
	
	local tbl = {
		func = "maps_list",
		msg = maps
	}
	writeTable(tbl)
end

function gsc.getGamemodes()
	local tbl = {
		func = "gamemodes_list",
		msg = engine.GetGamemodes()
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
		gsc.updateMap()
		gsc.updateGamemode()
		gsc.updateServerName()
	end,
	maps_list_request = function()
		gsc.getMaps()
	end,
	gamemodes_list_request = function()
		gsc.getGamemodes()
	end,
	funcs_request = function()
		local funcs = {}
		for k,v in pairs(gsc.commands) do
			local tbl = {}
			tbl.name = k
			tbl.description = v.description
			funcs[#funcs + 1] = tbl
		end
		
		local tbl = {
			rights = funcs
		}
		writeTable(tbl)
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

socket:open()

timer.Simple(3, function()
	if !socket:isConnected() then
		print("Connection failed! Socket Reconnecting activated...")
		timer.Create("Socket Reconnect", 1, 0, function()
			if socket:isConnected() then
				timer.Remove("Socket Reconnect")
				return
			end
			socket:open()
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
