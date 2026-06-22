--function gsc.CreateCommand
--	(name, description, callback, nicename, placeholder, is_player_func, getvaluefunc)

--name - unique name of the command
--description - description of the command. This will be displayed at owner menu at role creating 
--callback(data, ply) - function that will be called when admin will call this command at the panel
	--data - data that what got from the web-site(number of option or string)
	--ply - object of player on whom this command will be applied
--nicename - nicename of the command. This will displayed on web-site at choosing menues
--placeholder - function or string. If this is function this must return table of strings(options).
	--Placeholder is using for data entering setup on web-site
--is_player_func - when true this command will displayed at player card on web-site. If false
	--this command will displayed at global parameters list on web-site
--getvaluefunc - func that will called to get value of the global parameter to display it on web-site
	--if is_player_func is false. Should return text

local function getMaps()
	local maps = {}
	local files = file.Find("maps/*.bsp", "GAME")

    for _, filename in ipairs(files) do
        local mapName = string.gsub(filename, "%.bsp$", "")
        maps[#maps + 1] = mapName
    end
	
	return maps
end

local desc = "Allow switch maps"
gsc.CreateCommand("map", desc, function(map) 
	RunConsoleCommand("changelevel", getMaps()[map])
end, 'Map', getMaps, false, game.GetMap)

local function getGamemodeName()
	local curr_gamemode = engine.ActiveGamemode()
	for k, v in ipairs(engine.GetGamemodes()) do
		if v.name == curr_gamemode then
			return v.title
		end
	end
end

local function getGamemodes()
	local gamemodes = {}

	for k, v in ipairs(engine.GetGamemodes()) do
		gamemodes[#gamemodes + 1] = v.title
	end
	
	return gamemodes
end

desc = "Allow switch gamemodes"
gsc.CreateCommand("gamemode", desc, function(gamemode)
	RunConsoleCommand("gamemode", engine.GetGamemodes()[gamemode].name)
end, 'Gamemode', getGamemodes, false, getGamemodeName)

desc = "Allow kick players"
gsc.CreateCommand("kick", desc, function(reason, ply)
	ply:Kick(reason)
end, "Kick", "Enter the reason...", true)

desc = "Allow ban players"
local ban_times_names = {
	'5 mins', 
	'10 mins', 
	'15 mins', 
	'1 hour', 
	'1 day', 
	'1 week', 
	'1 month'
}
local ban_times = {
	5,
	10,
	15,
	60,
	1440,
	10080,
	43200
}
gsc.CreateCommand("ban", desc, function(mins_option, ply)
	ply:Ban(ban_times[mins_option], true)
end, "Ban", ban_times_names, true)