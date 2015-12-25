function calc_capacity()
{
	var msp = 1000000000;
	var cap = 0;
	var sp = msp;
	var tmp;
	var id;

	for (var i = 200; i < 230; i++)
	{
		id = "ship" + i;
		if (document.getElementsByName(id)[0])
		{
			cnt = parseInt($("*[name=" + id + "]").val());
			cap += cnt * parseInt($("*[name=capacity" + i + "]").val());
			if (cnt > 0)
			{
				tmp = parseInt($("*[name=speed" + i + "]").val());
				if (tmp < sp)
					sp = tmp;
			}
		}
	}
	if (cap <= 0)
		cap = "-";
	else
		cap = validate_number(cap);

	if ((sp <= 0) || (sp >= msp))
		sp = "-";
	else
		sp = validate_number(sp);

	$("#allcapacity").html(cap);
	$("#allspeed").html(sp);
}

function target()
{
	var galaxy = $("*[name=galaxy]").val();
	var system = $("*[name=system]").val();
	var planet = $("*[name=planet]").val();

	return("[" + galaxy + ":" + system + ":" + planet + "]");
}

function setTarget(galaxy, solarsystem, planet, planettype)
{
	$("*[name=galaxy]").val(galaxy);
	$("*[name=system]").val(solarsystem);
	$("*[name=planet]").val(planet);
	$('*[name=planettype]').val(planettype);
}

function setMission(mission)
{
	$('*[name=order]')[0].selectedIndex = mission;
}

function maxspeed()
{
	var msp = 1000000000;

	for (var i = 200; i < 230; i++)
	{
		if (document.getElementsByName("ship" + i)[0])
		{
			if ((parseInt($("*[name=speed" + i + "]").val())) >= 1 && (parseInt($("*[name=ship" + i + "]").val())) >= 1)
			{
				msp = Math.min(msp, parseInt($("*[name=speed" + i + "]").val()));
			}
		}
	}

	return msp;
}

function distance()
{
	var thisGalaxy = document.getElementsByName("thisgalaxy")[0].value;
	var thisSystem = document.getElementsByName("thissystem")[0].value;
	var thisPlanet = document.getElementsByName("thisplanet")[0].value;

	var targetGalaxy = document.getElementsByName("galaxy")[0].value;
	var targetSystem = document.getElementsByName("system")[0].value;
	var targetPlanet = document.getElementsByName("planet")[0].value;

	if ((targetGalaxy - thisGalaxy) != 0)
		return Math.abs(targetGalaxy - thisGalaxy) * 20000;

	if ((targetSystem - thisSystem) != 0)
		return Math.abs(targetSystem - thisSystem) * 5 * 19 + 2700;

	if ((targetPlanet - thisPlanet) != 0)
		return Math.abs(targetPlanet - thisPlanet) * 5 + 1000;

	return 5;
}

function duration()
{
	var speed = parseInt($("*[name=speed]").val());

	return Math.round((35000 / speed * Math.sqrt(distance() * 10 / maxspeed()) + 10) / XNova.fleetSpeed);
}

function consumption()
{
	var consumption = 0;
	var basicConsumption = 0;

	var distanceV = distance();
	var durationV = duration();

	if (durationV <= 1)
		durationV = 2;

	var shipspeed, spd;

	for (var i = 200; i < 230; i++)
	{
		if (document.getElementsByName("ship" + i)[0])
		{
			shipspeed = document.getElementsByName("speed" + i)[0].value;
			spd = 35000 / (durationV * XNova.fleetSpeed - 10) * Math.sqrt(distanceV * 10 / shipspeed);

			basicConsumption = document.getElementsByName("consumption" + i)[0].value * document.getElementsByName("ship" + i)[0].value;
			consumption += basicConsumption * distanceV / 35000 * ((spd / 10) + 1) * ((spd / 10) + 1);
		}
	}

	return Math.round(consumption) + 1;
}

function probeConsumption()
{
	var consumption = 0;
	var basicConsumption = 0;

	var distanceV = distance();
	var durationV = duration();

	if (document.getElementsByName("ship210")[0])
	{
		var shipspeed = document.getElementsByName("speed210")[0].value;
		var spd = 35000 / (durationV * XNova.fleetSpeed - 10) * Math.sqrt(distanceV * 10 / shipspeed);

		basicConsumption = document.getElementsByName("consumption210")[0].value * document.getElementsByName("ship210")[0].value;
		consumption += basicConsumption * distanceV / 35000 * ((spd / 10) + 1) * ((spd / 10) + 1);
	}

	return Math.round(consumption) + 1;
}

function unusedProbeStorage()
{
	var storage = document.getElementsByName('capacity210')[0].value * document.getElementsByName('ship210')[0].value;
	var stor = storage - probeConsumption();

	return (stor > 0) ? stor : 0;
}

function storage()
{
	var storage = 0;

	for (var i = 200; i < 300; i++)
	{
		if (document.getElementsByName("ship" + i)[0])
		{
			if (parseInt(document.getElementsByName("ship" + i)[0].value) >= 1)
			{
				storage += document.getElementsByName("ship" + i)[0].value * document.getElementsByName("capacity" + i)[0].value
			}
		}
	}

	storage -= consumption();

	if (document.getElementsByName("ship210")[0])
		storage -= unusedProbeStorage();

	return(storage);
}

function shortInfo()
{
	$('#distance').html(XNova.format(distance()));

	var seconds = duration();

	var hours = Math.floor(seconds / 3600);
	seconds -= hours * 3600;

	var minutes = Math.floor(seconds / 60);
	seconds -= minutes * 60;

	if (minutes < 10) minutes = "0" + minutes;
	if (seconds < 10) seconds = "0" + seconds;

	$("#duration").html(hours + ":" + minutes + ":" + seconds + " h");

	var stor = storage();
	var cons = consumption();

	$("#maxspeed").html(XNova.format(maxspeed()));

	if (stor >= 0)
	{
		$("#consumption").html('<font color="lime">' + XNova.format(cons) + '</font>');
		$("#storage").html('<font color="lime">' + XNova.format(stor) + '</font>');
	}
	else
	{
		$("#consumption").html('<font color="red">' + XNova.format(cons) + '</font>');
		$("#storage").html('<font color="red">' + XNova.format(stor) + '</font>');
	}

	durationTime = duration() * 1000;

	durationTimer();
}

var durationTime = 0;

function durationTimer()
{
	var D0 = new Date;
	hms('end_time', new Date(D0.getTime() + serverTime + durationTime));

	timeouts['durationTimer'] = setTimeout(durationTimer, 999);
}

function setResource(id, val)
{
	if (document.getElementsByName(id)[0])
	{
		document.getElementsByName("resource" + id)[0].value = val;
	}
}

function maxResource(id)
{
	var thisresource = parseInt(document.getElementsByName("thisresource" + id)[0].value);
	var thisresourcechosen = parseInt(document.getElementsByName("resource" + id)[0].value);

	if (isNaN(thisresourcechosen))
		thisresourcechosen = 0;

	if (isNaN(thisresource))
		thisresource = 0;

	var storCap = storage();

	if (id == 3)
		thisresource -= consumption();

	var metalToTransport 		= parseInt(document.getElementsByName("resource1")[0].value);
	var crystalToTransport 		= parseInt(document.getElementsByName("resource2")[0].value);
	var deuteriumToTransport 	= parseInt(document.getElementsByName("resource3")[0].value);

	if (isNaN(metalToTransport))
		metalToTransport = 0;

	if (isNaN(crystalToTransport))
		crystalToTransport = 0;

	if (isNaN(deuteriumToTransport))
		deuteriumToTransport = 0;

	var freeCapacity = Math.max(storCap - metalToTransport - crystalToTransport - deuteriumToTransport, 0);
	var cargo = Math.min(freeCapacity + thisresourcechosen, thisresource);

	if (document.getElementsByName("resource" + id)[0])
		document.getElementsByName("resource" + id)[0].value = cargo;

	calculateTransportCapacity();
}

function maxResources()
{
	var storCap = storage();

	var metalToTransport 		= document.getElementsByName("thisresource1")[0].value;
	var crystalToTransport 		= document.getElementsByName("thisresource2")[0].value;
	var deuteriumToTransport 	= document.getElementsByName("thisresource3")[0].value - consumption();

	var freeCapacity = storCap - metalToTransport - crystalToTransport - deuteriumToTransport;

	if (freeCapacity < 0)
	{
		metalToTransport = Math.min(metalToTransport, storCap);
		crystalToTransport = Math.min(crystalToTransport, storCap - metalToTransport);
		deuteriumToTransport = Math.min(deuteriumToTransport, storCap - metalToTransport - crystalToTransport);
	}

	document.getElementsByName("resource1")[0].value = Math.max(metalToTransport, 0);
	document.getElementsByName("resource2")[0].value = Math.max(crystalToTransport, 0);
	document.getElementsByName("resource3")[0].value = Math.max(deuteriumToTransport, 0);

	calculateTransportCapacity();
}

function maxShip(id)
{
	if (document.getElementsByName(id)[0])
		document.getElementsByName(id)[0].value = document.getElementsByName("max" + id)[0].value;
}

function maxShips()
{
	var id;

	for (var i = 200; i < 230; i++)
	{
		id = "ship" + i;
		maxShip(id);
	}
}

function noShip(id)
{
	if (document.getElementsByName(id)[0])
		document.getElementsByName(id)[0].value = 0;
}

function noShips()
{
	var id;

	for (var i = 200; i < 230; i++)
	{
		id = "ship" + i;
		noShip(id);
	}
}

function calculateTransportCapacity()
{
	var hold = 0;

	if (mission == 5 && $("select[name=holdingtime]").length)
	{
		var holdtime = $("select[name=holdingtime]").val();

		if (holdtime > 0)
		{
			hold = parseInt($('input[name=stayConsumption]').val()) * holdtime;
		}
	}

	var metal = Math.abs(document.getElementsByName("resource1")[0].value);
	var crystal = Math.abs(document.getElementsByName("resource2")[0].value);
	var deuterium = Math.abs(document.getElementsByName("resource3")[0].value);

	var transportCapacity = storage() - metal - crystal - deuterium - hold;

	if (transportCapacity < 0)
		$("#remainingresources").html("<font color=red>" + number_format(transportCapacity, 0, ',', '.') + "</font>");
	else
		$("#remainingresources").html("<font color=lime>" + number_format(transportCapacity, 0, ',', '.') + "</font>");

	return transportCapacity;
}

function abs(a)
{
	if (a < 0) return -a;
	return a;
}

function ACS(id)
{
	document.getElementsByName('acs')[0].value = id;
}

function t()
{
	var v = new Date();
	var n = new Date();
	var o = new Date();

	var bxx, ss, s, m, h;

	for (var cn = 1; cn <= anz; cn++)
	{
		bxx = $('#bxx' + cn);
		ss = bxx.attr('title');
		s = ss - Math.round((n.getTime() - v.getTime()) / 1000.);
		m = 0;
		h = 0;
		if (s < 0)
		{
			bxx.html("-");
		}
		else
		{
			if (s > 59)
			{
				m = Math.floor(s / 60);
				s = s - m * 60;
			}
			if (m > 59)
			{
				h = Math.floor(m / 60);
				m = m - h * 60;
			}
			if (s < 10)
			{
				s = "0" + s;
			}
			if (m < 10)
			{
				m = "0" + m;
			}
			bxx.html(h + ":" + m + ":" + s);
		}
		bxx.attr('title', ss - 1);
	}
	window.setTimeout("t();", 999);
}

function addZeros(value, count)
{
	var ret = "";
	var ost;
	for (i = 0; i < count; i++)
	{
		ost = value % 10;
		value = Math.floor(value / 10);
		ret = ost + ret;
	}
	return(ret);
}

function validate_number(value)
{
	if (value == 0)
	{
		ret = 0;
	}
	else
	{
		var inv;
		if (value < 0)
		{
			value = -value;
			inv = 1;
		}
		else
		{
			inv = 0;
		}

		var ret = "";
		var ost;

		while (value > 0)
		{
			ost = value % 1000;
			value = Math.floor(value / 1000);

			if (value <= 0)
				s_ost = ost;
			else
				s_ost = addZeros(ost, 3);

			if (ret == "")
				ret = s_ost;
			else
				ret = s_ost + "." + ret;
		}
		if (inv == 1)
		{
			ret = "-" + ret;
		}
	}
	return(ret);
}

function chShipCount(id, diff)
{
	diff = parseInt(diff);

	var ncur = parseInt(document.getElementsByName("ship" + id)[0].value);
	var count = ncur + diff;

	if (count < 0)
		count = 0;

	if (count > document.getElementsByName("maxship" + id)[0].value)
		count = document.getElementsByName("maxship" + id)[0].value;

	document.getElementsByName("ship" + id)[0].value = count;
}