<template>
	<div class="buldings-active">
		<div class="buldings-active-wrapper">
			<div class="buldings-active-image">
				<ModalLink navigate :href="'/info/' + item['id']">
					<img :src="'/assets/images/elements/' + item['id'] + '.webp'" :alt="item['name']">
				</ModalLink>
			</div>
			<div class="buldings-active-content">
				<div class="buldings-active-title">
					<ModalLink navigate :href="'/info/' + item['id']" aria-haspopup="dialog">
						{{ item['name'] }}
					</ModalLink>

					<Popper v-if="level" :content="$t('pages.research.current_level')">
						<span class="positive">
							{{ $formatNumber(level) }} <template v-if="item.max > 0">{{ $t('pages.research.from') }} <span class="neutral">{{ $formatNumber(item.max) }}</span></template>
						</span>
					</Popper>
				</div>
				<div v-if="available" class="flex items-center gap-1">
					<svg class="icon">
						<use xlink:href="/assets/images/symbols.svg#icon-time"></use>
					</svg>
					{{ $formatTime(item['time']) }}
				</div>

				<div v-if="item['effects']" class="buildings-effects-row">
					<Popper v-if="item.effects_resource === 'energy'" :content="$t('resources.energy')">
						<ResourceIcon code="energy" class="building-resource-icon resource-energy" role="img" :aria-label="$t('resources.energy')" focusable="false"/>
					</Popper>
					<span v-html="item['effects']" class="buildings-effects-row"></span>
				</div>

				<div v-if="props.item['available'] && !user.vacation" class="buldings-active-price">
					<span>{{ $t('pages.building.required_resources_level', { level: level + 1 }) }}</span>
					<BuildRowPrice :price="item['price']"/>
				</div>

				<div v-if="(item['available'] || typeof item['build'] === 'object') && !user.vacation" class="building-active-upgrade">
					<TechQueue v-if="typeof item['build'] === 'object'" :build="item['build']"/>
					<div v-else-if="item['max'] > 0 && item['max'] <= level" class="negative">
						{{ $t('pages.research.max_level') }}
					</div>
					<div v-else-if="!hasResources" class="negative text-center">
						{{ $t('pages.research.no_resources') }}
					</div>
					<button v-else-if="item['build'] !== true" @click.prevent="buildAction" :class="{ positive: level, negative: level === 0 }" class="button">
						{{ $t('pages.research.build') }}
					</button>
				</div>

				<div v-if="item['requirements']" class="building-active-requirements">
					<div class="title">{{ $t('pages.techtree.requirements') }}</div>
					<div class="items">
						<Popper v-for="req in item['requirements']" :content="req['name']">
							<div class="item" :style="{ backgroundImage: 'url(\'/assets/images/elements/' + req['id'] + '.webp\')' }">
								<div class="item-title">
									{{ req['level'] }} {{ req['diff'] !== 0 ? '(' + req['diff'] + ')' : '' }}
								</div>
							</div>
						</Popper>
					</div>
				</div>

				<div class="buldings-active-close" @click="emit('close')">
					<CloseIcon/>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import Popper from '~/components/Popper.vue';
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import useState from '~/composables/useState.js';
	import BuildRowPrice from '../Buildings/BuildRowPrice.vue';
	import { computed } from 'vue';
	import CloseIcon from '~/images/icons/close.svg?component';
	import TechQueue from '../Buildings/TechQueue.vue';
	import { useI18n } from 'vue-i18n';
	import { emptyFieldsCount } from '~/utils/buildings.js';
	import { ModalLink } from '@inertiaui/modal-vue';

	const props = defineProps({
		item: {
			type: Object,
		}
	})

	const { tm } = useI18n();

	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);

	const emit = defineEmits(['close', 'build']);

	const level = computed(() => user.value['technology'][props['item']['code']] || 0);

	const hasResources = computed(() => {
		return Object.keys(tm('resources')).every(res => {
			return !(typeof props.item.price[res] !== 'undefined' && planet.value['resources'][res] !== 'undefined' && props.item.price[res] > 0
				&& planet.value['resources'][res] && planet.value['resources'][res].value < props.item.price[res]);
		})
	});

	const available = computed(() => {
		return props.item['available'] && hasResources.value && emptyFieldsCount.value > 0
			&& !user.value.vacation;
	});

	async function buildAction () {
		emit('build');
	}
</script>