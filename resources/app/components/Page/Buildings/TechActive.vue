<template>
	<div class="buldings-active">
		<div class="buldings-active-wrapper">
			<div class="buldings-active-image">
				<Popup :id="item['id']">
					<img :src="'/assets/images/elements/' + item['id'] + '.webp'" :alt="item['name']">
				</Popup>
			</div>
			<div class="buldings-active-content">
				<div class="buldings-active-title">
					<Link :href="'/info/' + item['id']">
						{{ item['name'] }}
					</Link>

					<span v-if="level" class="positive" v-tooltip="$t('pages.research.current_level')">
						{{ $formatNumber(level) }} <template v-if="item.max > 0">{{ $t('pages.research.from') }} <span class="neutral">{{ $formatNumber(item.max) }}</span></template>
					</span>
				</div>
				<div v-if="available" class="flex items-center gap-1">
					<svg class="icon">
						<use xlink:href="/assets/images/symbols.svg#icon-time"></use>
					</svg>
					{{ $formatTime(item['time']) }}
				</div>

				<div v-if="item['effects']" v-html="item['effects']" class="buildings-effects-row"></div>

				<div v-if="props.item['available'] && !user.vacation" class="buldings-active-price">
					<span>Required resources for level {{ level + 1 }}</span>
					<BuildRowPrice :price="item['price']"/>
				</div>

				<div v-if="item['available'] && !user.vacation" class="building-active-upgrade">
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
					<div class="title">Требования</div>
					<div class="items">
						<div v-for="req in item['requirements']" class="item" :style="{ backgroundImage: 'url(\'/assets/images/elements/' + req['id'] + '.webp\')' }" v-tooltip="req['name']">
							<div class="item-title">
								{{ req['level'] }} {{ req['diff'] !== 0 ? '(' + req['diff'] + ')' : '' }}
							</div>
						</div>
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
	import BuildRowPrice from '../Buildings/BuildRowPrice.vue';
	import { computed } from 'vue';
	import Popup from '../Info/Popup.vue';
	import CloseIcon from '@assets/icons/close.svg?component';
	import TechQueue from '../Buildings/TechQueue.vue';
	import { Link, usePage } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';

	const props = defineProps({
		item: {
			type: Object,
		}
	})

	const { tm } = useI18n();

	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);
	const queue = computed(() => page.props.queue);

	const emit = defineEmits(['close', 'build']);

	function queueByType(type) {
		return queue.value.filter((item) => item.planet_id === planet.value?.id && item.type === type);
	}

	const fieldsEmpty = computed(() => {
		if (!planet.value) {
			return 0;
		}

		return planet.value.field_max - planet.value.field_used - queueByType('build').length;
	});
	
	const level = computed(() => user.value['technology'][props['item']['code']] || 0);

	const hasResources = computed(() => {
		return Object.keys(tm('resources')).every(res => {
			return !(typeof props.item.price[res] !== 'undefined' && planet.value['resources'][res] !== 'undefined' && props.item.price[res] > 0
				&& planet.value['resources'][res] && planet.value['resources'][res].value < props.item.price[res]);
		})
	});

	const available = computed(() => {
		return props.item['available'] && hasResources.value && fieldsEmpty.value > 0
			&& !user.value.vacation;
	});

	async function buildAction () {
		emit('build');
	}
</script>