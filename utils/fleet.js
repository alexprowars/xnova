//import { $get } from './api'

function distance (from, to)
{
	let distance = 5;

	if ((to['galaxy'] - from['galaxy']) !== 0)
		distance = Math.abs(to['galaxy'] - from['galaxy']) * 20000;
	else if ((to['system'] - from['system']) !== 0)
		distance = Math.abs(to['system'] - from['system']) * 5 * 19 + 2700;
	else if ((to['planet'] - from['planet']) !== 0)
		distance = Math.abs(to['planet'] - from['planet']) * 5 + 1000;

	return distance;
}

function speed (ships)
{
	let speed = 1000000000;

	ships.forEach((item) =>
	{
		if (!isNaN(parseInt(item['speed'])) && parseInt(item['speed']) > 0)
			speed = Math.min(speed, parseInt(item['speed']));
	});

	return speed;
}

function consumption (params)
{
	let consumption = 0;

	params.ships.forEach((item) =>
	{
		let speed = 35000 / (params.duration * params.universe_speed - 10) * Math.sqrt(params.distance * 10 / item['speed']);
		consumption += (item['consumption'] * item['count']) * params.distance / 35000 * ((speed / 10) + 1) * ((speed / 10) + 1);
	});

	return Math.round(consumption) + 1;
}

function duration (params)
{
	let duration = Math.round((35000 / params.factor * Math.sqrt(params.distance * 10 / params.max_speed) + 10) / params.universe_speed);

	if (duration <= 1)
		duration = 2;

	return duration;
}

function storage (ships)
{
	let storage = 0;

	ships.forEach((item) =>
	{
		storage += item['count'] * item['capacity'];
	});

	return storage;
}

function sendMission (_this, mission, galaxy, system, planet, type, count)
{
	return _this.$get('/fleet/quick/', {
		mission: mission,
		galaxy: galaxy,
		system: system,
		planet: planet,
		type: type,
		count: count
	})
	.then(result =>
	{
		_this.$store.commit('PAGE_LOAD', {
			messages: result.messages
		})
	})
}

export {
	distance,
	speed,
	consumption,
	duration,
	storage,
	sendMission
}