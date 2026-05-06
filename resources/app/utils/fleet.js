import { useErrorNotification } from '../composables/useToast.js';
import { useApiPost } from '../composables/useApi.js';

export function getDistance (from, to) {
	if ((to['galaxy'] - from['galaxy']) !== 0) {
		return Math.abs(to['galaxy'] - from['galaxy']) * 20000;
	} else if ((to['system'] - from['system']) !== 0) {
		return Math.abs(to['system'] - from['system']) * 5 * 19 + 2700;
	} else if ((to['planet'] - from['planet']) !== 0) {
		return Math.abs(to['planet'] - from['planet']) * 5 + 1000;
	}

	return 5;
}

export function getSpeed (ships) {
	let speed = 1000000000

	ships.forEach((item) => {
		if (!isNaN(parseInt(item['speed'])) && parseInt(item['speed']) > 0) {
			speed = Math.min(speed, parseInt(item['speed']));
		}
	})

	return speed
}

export function getConsumption (params) {
	const consumption = params.ships.reduce((sum, item) => {
		let speed = 35000 / (params.duration * params.universe_speed - 10) * Math.sqrt(params.distance * 10 / item['speed'])

		return sum + (item['consumption'] * item['count']) * params.distance / 35000 * ((speed / 10) + 1) * ((speed / 10) + 1)
	}, 0)

	return Math.round(consumption) + 1
}

export function getDuration (params) {
	let duration = Math.round((35000 / params.factor * Math.sqrt(params.distance * 10 / params.max_speed) + 10) / params.universe_speed);

	if (duration <= 1) {
		duration = 2;
	}

	return duration;
}

export function getStorage (ships) {
	return ships.reduce((sum, item) => sum + item['count'] * item['capacity'], 0)
}

export async function sendMission(mission, galaxy, system, planet, type, count) {
	try {
		await useApiPost('/fleet/quick', {
			mission, galaxy, system, planet, type, count
		});
	} catch (e) {
		useErrorNotification(e.message);
	}
}