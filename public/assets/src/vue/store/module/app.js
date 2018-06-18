import axios from 'axios'

const state = Object.assign({}, {
	mobile: /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)
}, options)

const mutations = {
	PAGE_LOAD (state, data)
	{
		application.start_time = Math.floor(((new Date()).getTime()) / 1000)

		for (let key in data)
		{
			if (data.hasOwnProperty(key))
				state[key] = data[key];
		}
	}
}

const actions = {
	loadPage ({commit}, url)
	{
		return new Promise((resolve, reject) =>
		{
			axios({
				method: 'get',
				url: url,
				timeout: 10000,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
			})
			.then(result =>
			{
				if (result.data.data !== undefined)
				{
					commit('PAGE_LOAD', result.data.data)
					resolve(result.data.data)
				}
				else
					reject()
			})
			.catch((error) => {
				reject(error)
			})
		})
	}
}

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