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
import MerchantController from './../controllers/merchant/merchant.vue'
import AllianceChatController from './../controllers/alliance/alliance_chat.vue'
import PlayersStatController from './../controllers/players/players_stat.vue'
import NotesController from './../controllers/notes/notes.vue'
import PhalanxController from './../controllers/phalanx/phalanx.vue'
import IndexController from './../controllers/index/index.vue'
import ResourcesController from './../controllers/resources/resources.vue'
import OfficierController from './../controllers/officier/officier.vue'
import CreditsController from './../controllers/credits/credits.vue'
import TechController from './../controllers/tech/tech.vue'
import TechInfoController from './../controllers/tech/tech-info.vue'
import SupportController from './../controllers/support/support.vue'
import StatController from './../controllers/stat/stat.vue'
import ImperiumController from './../controllers/imperium/imperium.vue'
import HtmlController from './../controllers/html.vue'

export default new VueRouter({
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
		path: '/fleet*',
		component: FleetIndexController
	}, {
		path: '/overview*',
		component: OverviewController
	}, {
		path: '/chat',
		component: ChatController
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
		path: '/tech/:tech_id',
		component: TechInfoController
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
		path: '/alliance/chat',
		component: AllianceChatController
	}, {
		path: '/alliance/stat/id/:ally_id',
		component: PlayersStatController
	}, {
		path: '/players/stat/:user_id',
		component: PlayersStatController
	}, {
		path: '/',
		component: IndexController
	}, {
		path: '*',
		component: HtmlController
	}]
})