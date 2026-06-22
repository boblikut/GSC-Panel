gsc = gsc or {}
gsc.commands = gsc.commands or {}

function gsc.CreateCommand(name, description, callback, nicename, placeholder, is_player_func, getvaluefunc)
	gsc.commands[name] = {
		callback = callback,
		nicename = nicename or string.NiceName( name ),
		placeholder = placeholder or "Enter the value...",
		getvaluefunc = getvaluefunc,
		description = description or name,
		is_player_func = is_player_func
	}
end

function gsc.CallCommand(name, argument, userID)
	if userID == 0 then
		gsc.commands[name].callback(argument)
		timer.Create("GSC Update Commands", 0.1, 1, gsc.updateCommands)
	else
		local ply = Player(userID)
		if !ply then return end
		gsc.commands[name].callback(argument, ply)
	end
end