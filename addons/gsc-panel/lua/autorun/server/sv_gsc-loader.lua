include('gsc/sv_gsc-commands.lua')
include('gsc/sv_gsc-panel.lua')
include('gsc/sv_gsc-info.lua')
for _, file in ipairs(file.Find("gsc/plugins/*.lua", "LUA")) do
    include("gsc/plugins/" .. file)
end