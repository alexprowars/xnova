export default {
	getServerTime: state => () => {
		return Math.floor((new Date).getTime() / 1000) + state.stats.time - state.start_time;
	},
	menuActiveLink: state => {
		return state['route']['controller']+(state['route']['controller'] === 'buildings' ? state['route']['action'] : '');
	},
	isAuthorized: state => () => {
		return state.user && state.user.id > 0
	}
}