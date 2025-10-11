gsc = gsc or {}
gsc.commands = gsc.commands or {}

function gsc.CreateCommand(name, description, callback, nicename, placeholder, is_player_command)
	gsc.commands[name] = {
		callback = callback,
		nicename = nicename or string.NiceName( name ),
		placeholder = placeholder or "Enter the value...",
		is_player_command = is_player_command,
		description = description or name
	}
end

function gsc.CallCommand(name, argument, userID)
	if userID == 0 then
		gsc.commands[name].callback(argument)
	else
		local ply = Player(userID)
		if !ply then return end
		gsc.commands[name].callback(argument, ply)
	end
end

local desc = "Allow switch maps"
gsc.CreateCommand("map", desc, function(map) 
	RunConsoleCommand("changelevel", map)
end)

local desc = "Allow switch gamemodes"
gsc.CreateCommand("gamemode", desc, function(gamemode)
	RunConsoleCommand("gamemode", gamemode)
	timer.Create("GSC gamemode update", 0.1, 1, function() 
		gsc.updateGamemode()
	end)
end)

local desc = "Allow kick players"
gsc.CreateCommand("kick", desc, function(reason, ply)
	ply:Kick(reason)
end, "Kick", "Enter the reason...", true)

desc = "Allow ban players"
gsc.CreateCommand("ban", desc, function(mins, ply)
	ply:Ban(mins, true)
end, "Ban", "Enter how long to ban a person(in mins)", true)
