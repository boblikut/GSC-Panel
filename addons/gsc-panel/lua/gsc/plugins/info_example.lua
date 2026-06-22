--function gsc.AddGSCInfo(nicename, getinfofunc)

--nicename - at the same time unqiue name of info and nicename to show on web-site at data table of the player
--getinfofunc - function to get a player info. Should return string

local PLAYER = FindMetaTable("Player")

gsc.AddGSCInfo('SteamID', PLAYER.SteamID)
gsc.AddGSCInfo('User group', PLAYER.GetUserGroup)