import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import BuildingBuildController from './../controllers/buildings/build.vue'
import BuildingTechController from './../controllers/buildings/tech.vue'
import BuildingUnitController from './../controllers/buildings/unit.vue'
import GalaxyController from './../controllers/galaxy/galaxy.vue'
import OverviewController from './../controllers/overview/overview.vue'
import FleetIndexController from './../controllers/fleet/fleet-index.vue'
import FleetOneController from './../controllers/fleet/fleet-one.vue'
import FleetTwoController from './../controllers/fleet/fleet-two.vue'
import ChatController from './../controllers/chat/chat.vue'
import MessagesController from './../controllers/messages/messages.vue'
import MessagesWriteController from './../controllers/messages/messages-write.vue'
import MerchantController from './../controllers/merchant/merchant.vue'
import AllianceChatController from './../controllers/alliance/alliance_chat.vue'
import NotesController from './../controllers/notes/notes.vue'
import PhalanxController from './../controllers/phalanx/phalanx.vue'
import IndexController from './../controllers/index/index.vue'
import ResourcesController from './../controllers/resources/resources.vue'
import OfficierController from './../controllers/officier/officier.vue'
import CreditsController from './../controllers/credits/credits.vue'
import TechController from './../controllers/tech/tech.vue'
import SupportController from './../controllers/support/support.vue'
import StatController from './../controllers/stat/stat.vue'
import ImperiumController from './../controllers/imperium/imperium.vue'
import RegistrationController from './../controllers/index/index-registration.vue'
import RemindController from './../controllers/index/index-remind.vue'
import BannedController from './../controllers/banned/banned.vue'
import ContactsController from './../controllers/contacts/contacts.vue'
import RecordsController from './../controllers/records/records.vue'
import SimController from './../controllers/sim/sim.vue'
import TextController from './../controllers/text.vue'
import HtmlController from './../controllers/html.vue'

const router = new VueRouter({
	mode: 'history',
	routes: [{
		path: '/buildings/research*',
		component: BuildingTechController
	}, {
		path: '/buildings/fleet*',
		component: BuildingUnitController
	}, {
		path: '/buildings/defense*',
		component: BuildingUnitController
	}, {
		path: '/buildings*',
		component: BuildingBuildController
	}, {
		path: '/galaxy*',
		component: GalaxyController
	}, {
		path: '/phalanx*',
		component: PhalanxController
	}, {
		path: '/fleet/one',
		component: FleetOneController
	}, {
		path: '/fleet/two',
		component: FleetTwoController
	}, {
		path: '/fleet/shortcut/add*',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/fleet/fleet-shortcut-add.vue')
	}, {
		path: '/fleet/shortcut/view*',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/fleet/fleet-shortcut-add.vue')
	}, {
		path: '/fleet/shortcut*',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/fleet/fleet-shortcut.vue')
	}, {
		path: '/fleet/verband*',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/fleet/fleet-verband.vue')
	}, {
		path: '/fleet*',
		component: FleetIndexController
	}, {
		path: '/overview/rename*',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/overview/overview-rename.vue')
	}, {
		path: '/overview/delete*',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/overview/overview-delete.vue')
	}, {
		path: '/overview*',
		component: OverviewController
	}, {
		path: '/chat',
		component: ChatController
	}, {
		path: '/messages/write/*',
		component: MessagesWriteController
	}, {
		path: '/messages',
		component: MessagesController
	}, {
		path: '/merchant',
		component: MerchantController
	}, {
		path: '/imperium',
		component: ImperiumController
	}, {
		path: '/resources*',
		component: ResourcesController
	}, {
		path: '/officier*',
		component: OfficierController
	}, {
		path: '/credits*',
		component: CreditsController
	}, {
		path: '/info*',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/info/info.vue')
	}, {
		path: '/tech/:tech_id',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/tech/tech-info.vue')
	}, {
		path: '/tech*',
		component: TechController
	}, {
		path: '/support',
		component: SupportController
	}, {
		path: '/stat*',
		component: StatController
	}, {
		path: '/notes',
		component: NotesController
	}, {
		path: '/race',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/race/race.vue')
	}, {
		path: '/alliance/chat',
		component: AllianceChatController
	}, {
		path: '/alliance/stat/id/:ally_id',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/players/players_stat.vue')
	}, {
		path: '/players/stat/:user_id',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/players/players_stat.vue')
	}, {
		path: '/players/:user_id',
		component: () => import(/* webpackChunkName: "app-other" */ './../controllers/players/players.vue')
	}, {
		path: '/registration/',
		component: RegistrationController
	}, {
		path: '/remind/',
		component: RemindController
	}, {
		path: '/banned/',
		component: BannedController
	}, {
		path: '/contacts/',
		component: ContactsController
	}, {
		path: '/records/',
		component: RecordsController
	}, {
		path: '/sim/',
		component: SimController
	}, {
		path: '/content/*',
		component: TextController
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