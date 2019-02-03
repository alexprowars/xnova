import app from 'app'

const state = Object.assign({}, {
	loaded: false,
	mobile: /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)
}, options)

const mutations = {
	PAGE_LOAD (state, data)
	{
		app.start_time = Math.floor(((new Date()).getTime()) / 1000)

		for (let key in data)
		{
			if (data.hasOwnProperty(key))
			{
				state[key] = data[key];

				if (key === 'page')
					state.loaded = false
			}
		}
	}
}

const actions = {}

const getters = {
	menuActiveLink: state => {
		return state['route']['controller']+(state['route']['controller'] === 'buildings' ? state['route']['action'] : '');
	},
}

export {
	state,
	mutations,
	actions,
	getters
}