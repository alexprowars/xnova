import { getLocation } from '~/utils/helpers'
const { parse } = require('querystring')

let timer

export default {
	nuxtServerInit (store, context)
	{
		const headers = context.req && context.req.headers

		if (headers.cookie === undefined)
			headers.cookie = ''

		if (context.req.method === 'POST')
		{
			let body = new Promise((resolve) =>
			{
				let body = '';

				context.req.on('data', chunk => {
					body += chunk.toString();

					if (body.length > 1e6)
						context.req.connection.destroy();
				});

				context.req.on('end', async () => {
					resolve(parse(body));
				});
			});

			return body.then((body) =>
			{
				body['initial'] = 'Y';

				return context.app.$post(context.route.fullPath, body).then((data) =>
				{
					if (data.redirect && data.redirect.length > 0)
						return context.redirect(data.redirect)

					for (let key in data)
					{
						if (data.hasOwnProperty(key))
							store.state[key] = data[key];
					}

					if (data.route && data.route.controller === 'error')
						throw new Error('Страница не найдена')
				})
				.catch((e) => {
					return context.error(e);
				})
			});
		}
		else
		{
			return context.app.$get(context.route.fullPath, {
				initial: 'Y'
			})
			.then((data) =>
			{
				if (data.redirect && data.redirect.length > 0)
					return context.redirect(data.redirect)

				for (let key in data)
				{
					if (data.hasOwnProperty(key))
						store.state[key] = data[key];
				}

				if (data.route && data.route.controller === 'error')
					throw new Error('Страница не найдена')
			})
			.catch((e) => {
				return context.error(e);
			})
		}
	},
	loadPage ({ state, commit }, url)
	{
		if (typeof url === 'undefined')
			url = this.app.context.route.fullPath

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
				})
			})
		}

		clearTimeout(timer)
		timer = setTimeout(() => {
			commit('setLoadingStatus', true)
		}, 1000)

		if (this.app.context.req && this.app.context.req.method === 'POST')
			return

		return this.$get(url).then((data) =>
		{
			let loc = getLocation(url)

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
					})
				}

				if (typeof data['tutorial'] !== 'undefined' && data['tutorial']['toast'] !== '')
				{
					this.$toasted.show(data['tutorial']['toast'], {
						type: 'info'
					})
				}

				let page = JSON.parse(JSON.stringify(data.page))

				delete data.page

				clearTimeout(timer)

				commit('PAGE_LOAD', data)
				commit('setLoadingStatus', false)

				return {
					page
				}
			}
		})
	}
}