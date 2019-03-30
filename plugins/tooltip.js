import Vue from 'vue'
import Popper from './../components/views/popper.vue'
import { VTooltip } from 'v-tooltip'
import merge from 'lodash.merge'

export default ({ store }) =>
{
	const finalOptions = {}
	merge(finalOptions, VTooltip.options, {
		defaultDelay: 100,
		defaultTrigger: store.getters.isMobile ? 'click' : 'hover focus',
	})

	VTooltip.options = finalOptions

	Vue.directive('tooltip', VTooltip)
	Vue.component('Popper', Popper)
}