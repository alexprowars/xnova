export default {
	getServerTime: state => () => {
		return Math.floor((new Date).getTime() / 1000) + state.stats.time - state.start_time;
	},
	isAuthorized: state => () => {
		return state.user && state.user.id > 0
	},
	isMobile: _ => {
		return typeof window !== 'undefined' ? /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(window.navigator.userAgent) : false
	},
}