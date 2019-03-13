import { getLocation } from '~/utils/helpers'

export default {
	nuxtServerInit (store, context)
	{
		const headers = context.req && context.req.headers;

		if (headers.cookie === undefined)
			headers.cookie = '';

		return context.app.$get(context.route.fullPath, {
			initial: 'Y'
		})
		.then((data) =>
		{
			for (let key in data)
			{
				if (data.hasOwnProperty(key))
				{
					store.state[key] = data[key];
				}
			}
		});
	},
	loadPage ({ state, commit }, url)
	{
		if (state.page !== null)
		{
			let page = JSON.parse(JSON.stringify(state.page))

			commit('PAGE_LOAD', {
				page: null
			})

			return new Promise((resolve) =>
			{
				return resolve({
					page
				});
			})
		}

		commit('setLoadingStatus', true)

		return this.$get(url).then((data) =>
		{
			let loc = getLocation(url);

			if (loc['pathname'] !== data['url'])
				this.$router.replace(data['url'])
			else
			{
				if (typeof data['tutorial'] !== 'undefined' && data['tutorial']['popup'] !== '')
				{
					$.confirm({
						title: 'Обучение',
						content: data['tutorial']['popup'],
						confirmButton: 'Продолжить',
						cancelButton: false,
						backgroundDismiss: false,
						confirm: () =>
						{
							if (data['tutorial']['url'] !== '')
								this.$router.push(data['tutorial']['url']);
						}
					});
				}

				if (typeof data['tutorial'] !== 'undefined' && data['tutorial']['toast'] !== '')
				{
					this.$toasted.show(data['tutorial']['toast'], {
						type: 'info'
					});
				}

				let page = JSON.parse(JSON.stringify(data.page))

				delete data.page

				commit('PAGE_LOAD', data)
				commit('setLoadingStatus', false)

				return {
					page
				};
			}
		});
	}
};