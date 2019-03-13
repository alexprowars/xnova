import Vue from 'vue'
import VTooltip from 'v-tooltip'

export default ({ store }) =>
{
	VTooltip.enabled = !store.getters.isMobile

	Vue.use(VTooltip, {
		defaultDelay: 100,
		defaultTrigger: store.getters.isMobile ? 'click' : 'hover focus',
		popover: {
			defaultDelay: 100,
			defaultTrigger: store.getters.isMobile ? 'click' : 'hover focus',
		}
	})
}