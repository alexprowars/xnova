export function getDistance (from, to)
{
	if ((to['galaxy'] - from['galaxy']) !== 0)
		return Math.abs(to['galaxy'] - from['galaxy']) * 20000
	else if ((to['system'] - from['system']) !== 0)
		return Math.abs(to['system'] - from['system']) * 5 * 19 + 2700
	else if ((to['planet'] - from['planet']) !== 0)
		return Math.abs(to['planet'] - from['planet']) * 5 + 1000

	return 5
}

export function getSpeed (ships)
{
	let speed = 1000000000

	ships.forEach((item) =>
	{
		if (!isNaN(parseInt(item['speed'])) && parseInt(item['speed']) > 0)
			speed = Math.min(speed, parseInt(item['speed']))
	})

	return speed
}

export function getConsumption (params)
{
	const consumption = params.ships.reduce((sum, item) =>
	{
		let speed = 35000 / (params.duration * params.universe_speed - 10) * Math.sqrt(params.distance * 10 / item['speed'])

		return sum + (item['consumption'] * item['count']) * params.distance / 35000 * ((speed / 10) + 1) * ((speed / 10) + 1)
	}, 0)

	return Math.round(consumption) + 1
}

export function getDuration (params)
{
	let duration = Math.round((35000 / params.factor * Math.sqrt(params.distance * 10 / params.max_speed) + 10) / params.universe_speed)

	if (duration <= 1)
		duration = 2

	return duration
}

export function getStorage (ships)
{
	return ships.reduce((sum, item) => {
		return sum + item['count'] * item['capacity']
	}, 0)
}

export function sendMission (_this, mission, galaxy, system, planet, type, count)
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