gsc.info = gsc.info or {}

function gsc.AddGSCInfo(nicename, getinfofunc)
	gsc.info[nicename] = getinfofunc
end

function gsc.GetGSCInfo()
	return gsc.info
end