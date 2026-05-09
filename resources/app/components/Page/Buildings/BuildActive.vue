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

					<span v-if="level" class="positive" v-tooltip="$t('pages.building.current_level')">
						{{ $formatNumber(level) }}
					</span>
				</div>
				<div v-if="available" class="flex gap-4">
					<div class="flex items-center justify-center gap-1">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-time"></use>
						</svg>
						{{ $formatTime(item['time']) }}
					</div>
					<div v-if="item['exp'] > 0" class="flex items-center justify-center gap-1" title="Опыт">
						<svg class="icon">
							<use xlink:href="/assets/images/symbols.svg#icon-exp"></use>
						</svg>
						{{ $formatNumber(item['exp']) }} exp
					</div>
				</div>

				<div v-if="item['effects']" class="buldings-active-production">
					<span>Production</span>
					<div class="flex gap-2">
						<template v-for="(value, resource) in item['effects']">
							<div v-if="value !== 0" class="flex items-center gap-1">
								<span :class="'sprite skin_s_'+resource" :title="$t('resources.' + resource)"></span>
								<span :class="{ positive: value > 0, negative: value < 0 }">{{ value > 0 ? '+' : '' }}{{ $formatNumber(value) }}</span>
							</div>
						</template>
					</div>
				</div>

				<div v-if="available" class="buldings-active-price">
					<span>Required resources for level {{ level + 1 }}</span>
					<BuildRowPrice :price="item['price']"/>
				</div>

				<div v-if="item['available'] && !user.vacation" class="building-active-upgrade">
					<div v-if="emptyFieldsCount <= 0" class="negative">
						{{ $t('pages.building.status_no_more_fields') }}
					</div>
					<a v-else-if="user['queue_max'] > 1 && queueByType('build').length > 0" @click.prevent="buildAction">
						{{ $t('pages.building.status_add_to_list') }}
					</a>
					<div v-else-if="!hasResources" class="negative text-center">
						{{ $t('pages.building.status_no_resources') }}
					</div>
					<div v-else-if="user['queue_max'] <= queueByType('build').length" class="negative">
						{{ $t('pages.building.status_queue_full') }}
					</div>
					<button v-else-if="queueByType('build').length === 0"  @click.prevent="buildAction" class="button">
						{{ level === 0 ? $t('pages.building.action_build') : $t('pages.building.action_improve') }}
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
	import BuildRowPrice from './BuildRowPrice.vue';
	import { computed } from 'vue';
	import Popup from '../Info/Popup.vue';
	import CloseIcon from '~/images/icons/close.svg?component';
	import { useI18n } from 'vue-i18n';
	import { Link, usePage } from '@inertiajs/vue3';
	import { queueByType, emptyFieldsCount } from '~/utils/buildings.js';

	const props = defineProps({
		item: {
			type: Object,
		}
	})

	const { tm } = useI18n();
	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);
	const emit = defineEmits(['close', 'build']);

	const level = computed(() => planet.value['buildings'][props['item']['code']] || 0);

	const hasResources = computed(() => {
		return Object.keys(tm('resources')).every(res => {
			if (typeof props.item.price[res] !== 'undefined' && typeof planet.value['resources'][res] !== 'undefined' && props.item.price[res] > 0) {
				if (res === 'energy') {
					if (planet.value['resources'][res].capacity < props.item.price[res]) {
						return false
					}
				} else if (planet.value['resources'][res].value < props.item.price[res]) {
					return false
				}
			}

			return true
		})
	});

	const available = computed(() => {
		return props.item['available'] && hasResources.value && emptyFieldsCount.value > 0
			&& !user.value.vacation;
	});

	async function buildAction () {
		emit('build', props.item['id']);
	}
</script>