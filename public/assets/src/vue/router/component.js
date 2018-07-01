import app from 'app'
import Vue from 'vue'
import { loadPage } from './../js/helpers.js'

const router = {
	data () {
		return {
			page: null
		}
	},
	methods: {
		setPageData (data) {
			this.page = data
		},
	},
	beforeRouteEnter (to, from, next)
	{
		Vue.nextTick(() =>
		{
			if (!app.$store.state.loaded)
			{
				if (app.$store.state.page)
				{
					let data = JSON.parse(JSON.stringify(app.$store.state.page));

					app.$store.commit('PAGE_LOAD', {
						page: false
					})

					return next(vm => {
						vm.$store.state.loaded = true
						vm.setPageData(data)
						vm.afterLoad && vm.afterLoad()
					});
				}

				if (app.$store.state.error)
				{
					return next(vm => {
						vm.$store.state.loaded = true
						vm.setPageData(null)
						vm.afterLoad && vm.afterLoad()
					});
				}
			}

			loadPage(to.fullPath).then((data) =>
			{
				if (to.path !== data['url'])
					app.$router.replace(data['url'])
				else
				{
					let pageData = JSON.parse(JSON.stringify(data.page));

					delete data.page

					app.$store.commit('PAGE_LOAD', data)

					return next(vm => {
						vm.setPageData(pageData)
						vm.afterLoad && vm.afterLoad()
					})
				}
			})
		})
	},
	beforeRouteUpdate (to, from, next)
	{
		if (!app.$store.state.loaded)
		{
			if (app.$store.state.page)
			{
				let data = JSON.parse(JSON.stringify(app.$store.state.page));

				app.$store.commit('PAGE_LOAD', {
					page: false
				})

				app.$store.state.loaded = true
				this.setPageData(data)
				this.afterLoad && this.afterLoad()

				return next();
			}

			if (app.$store.state.error)
			{
				app.$store.state.loaded = true
				this.setPageData(null)
				this.afterLoad && this.afterLoad()

				return next()
			}
		}

		loadPage(to.fullPath).then((data) =>
		{
			next()

			if (to.path !== data['url'])
			{
				Vue.nextTick(() => {
					app.$router.replace(data['url'])
				})
			}
			else
			{
				let pageData = JSON.parse(JSON.stringify(data.page));

				delete data.page

				app.$store.commit('PAGE_LOAD', data)
				this.setPageData(pageData)
				this.afterLoad && this.afterLoad()
			}
		})
	}
}
export default router