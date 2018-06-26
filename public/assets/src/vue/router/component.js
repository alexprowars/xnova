import app from 'app'
import Vue from 'vue'

const router = {
	data () {
		return {
			page: null
		}
	},
	fetchData (url)
	{
		app.loadPage(url).then((data) => {
			this.setPageData(data.page)
		});
	},
	setPageData (data) {
		this.page = data
	},
	beforeRouteEnter (to, from, next)
	{
		Vue.nextTick(() =>
		{
			if (app.$store.state.page)
			{
				let data = JSON.parse(JSON.stringify(app.$store.state.page));

				app.$store.commit('PAGE_LOAD', {
					page: false
				})

				return next(vm => {
					vm.page = data
					vm.afterLoad && vm.afterLoad()
				});
			}

			app.loadPage(to.fullPath).then((data) =>
			{
				if (to.path !== data['url'])
					app.$router.replace(data['url'])
				else
				{
					app.$store.commit('PAGE_LOAD', data)

					next(vm => {
						vm.page = data.page
						vm.afterLoad && vm.afterLoad()
					})
				}
			})
		})
	},
	beforeRouteUpdate (to)
	{
		app.loadPage(to.fullPath).then((data) =>
		{
			if (to.path !== data['url'])
				app.$router.replace(data['url'])
			else
			{
				app.$store.commit('PAGE_LOAD', data)
				this.page = data.page
				this.afterLoad && this.afterLoad()
			}
		})
	}
}
export default router