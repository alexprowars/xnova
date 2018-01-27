function target()
{
	var galaxy = $("*[name=galaxy]").val();
	var system = $("*[name=system]").val();
	var planet = $("*[name=planet]").val();

	return("[" + galaxy + ":" + system + ":" + planet + "]");
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

	return Math.round((35000 / speed * Math.sqrt(distance() * 10 / maxspeed()) + 10) / options['speed']['fleet']);
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
			spd = 35000 / (durationV * options['speed']['fleet'] - 10) * Math.sqrt(distanceV * 10 / shipspeed);

			basicConsumption = document.getElementsByName("consumption" + i)[0].value * document.getElementsByName("ship" + i)[0].value;
			consumption += basicConsumption * distanceV / 35000 * ((spd / 10) + 1) * ((spd / 10) + 1);
		}
	}

	return Math.round(consumption) + 1;
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

	return(storage);
}

var durationTime = 0;

function durationTimer()
{
	var D0 = new Date;

	$("#end_time").html(date('d.m.Y H:i:s', D0.getTime() + serverTime + durationTime))

	timeouts['durationTimer'] = setTimeout(durationTimer, 1000);
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