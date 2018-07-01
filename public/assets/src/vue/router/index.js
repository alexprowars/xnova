import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import IndexController from './../controllers/index/index.vue'
import RegistrationController from './../controllers/index/index-registration.vue'
import RemindController from './../controllers/index/index-remind.vue'
import HtmlController from './../controllers/html.vue'

const router = new VueRouter({
	mode: 'history',
	routes: [{
		path: '/buildings/research*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/buildings/tech.vue')
	}, {
		path: '/buildings/fleet*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/buildings/unit.vue')
	}, {
		path: '/buildings/defense*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/buildings/unit.vue')
	}, {
		path: '/buildings*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/buildings/build.vue')
	}, {
		path: '/galaxy*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/galaxy/galaxy.vue')
	}, {
		path: '/phalanx*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/phalanx/phalanx.vue')
	}, {
		path: '/fleet/one',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/fleet/fleet-one.vue')
	}, {
		path: '/fleet/two',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/fleet/fleet-two.vue')
	}, {
		path: '/fleet/shortcut/add*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/fleet/fleet-shortcut-add.vue')
	}, {
		path: '/fleet/shortcut/view*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/fleet/fleet-shortcut-add.vue')
	}, {
		path: '/fleet/shortcut*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/fleet/fleet-shortcut.vue')
	}, {
		path: '/fleet/verband*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/fleet/fleet-verband.vue')
	}, {
		path: '/fleet*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/fleet/fleet-index.vue')
	}, {
		path: '/overview/rename*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/overview/overview-rename.vue')
	}, {
		path: '/overview/delete*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/overview/overview-delete.vue')
	}, {
		path: '/overview*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/overview/overview.vue')
	}, {
		path: '/chat',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/chat/chat.vue')
	}, {
		path: '/messages/write/*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/messages/messages-write.vue')
	}, {
		path: '/messages',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/messages/messages.vue')
	}, {
		path: '/merchant',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/merchant/merchant.vue')
	}, {
		path: '/imperium',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/imperium/imperium.vue')
	}, {
		path: '/resources*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/resources/resources.vue')
	}, {
		path: '/officier*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/officier/officier.vue')
	}, {
		path: '/credits*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/credits/credits.vue')
	}, {
		path: '/info*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/info/info.vue')
	}, {
		path: '/tech/:tech_id',
		component: () => import(/* webpackChunkName: "app/view-other" */ './../controllers/tech/tech-info.vue')
	}, {
		path: '/tech*',
		component: () => import(/* webpackChunkName: "app/view-other" */ './../controllers/tech/tech.vue')
	}, {
		path: '/support',
		component: () => import(/* webpackChunkName: "app/view-other" */ './../controllers/support/support.vue')
	}, {
		path: '/stat*',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/stat/stat.vue')
	}, {
		path: '/notes',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/notes/notes.vue')
	}, {
		path: '/race',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/race/race.vue')
	}, {
		path: '/alliance/chat',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/alliance/alliance_chat.vue')
	}, {
		path: '/alliance/stat/id/:ally_id',
		component: () => import(/* webpackChunkName: "app/view-other" */ './../controllers/players/players_stat.vue')
	}, {
		path: '/players/stat/:user_id',
		component: () => import(/* webpackChunkName: "app/view-other" */ './../controllers/players/players_stat.vue')
	}, {
		path: '/players/:user_id',
		component: () => import(/* webpackChunkName: "app/view-main" */ './../controllers/players/players.vue')
	}, {
		path: '/registration/',
		component: RegistrationController
	}, {
		path: '/remind/',
		component: RemindController
	}, {
		path: '/',
		component: IndexController
	}, {
		path: '*',
		component: HtmlController
	}]
})

router.beforeEach((to, from, next) =>
{
	if (from.name === null && typeof from.name === "object")
		return next();

	if (router.app.request_block)
		return next(false);

	if (window.jconfirm)
	{
		window.jconfirm.instances.forEach((item) => {
			item.close();
		});
	}

	return next();
})

export default router