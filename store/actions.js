export default {
	nuxtServerInit (store, context)
	{
		const headers = context.req && context.req.headers;

		if (headers.cookie === undefined)
			headers.cookie = '';

		return context.app.$get('/', {

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
	loadPage ({ commit }, url)
	{
		commit('setLoadingStatus', true)

		return this.$get(url).then((data) =>
		{
			if (url !== data['url'])
				this.$router.replace(data['url'])
			else
			{
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