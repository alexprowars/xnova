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

					<span :class="{ positive: level > 0, negative: level === 0 }">{{ $formatNumber(level) }}</span>
				</div>
				<div v-if="available" class="flex items-center gap-1">
					<svg class="icon">
						<use xlink:href="/assets/images/symbols.svg#icon-time"></use>
					</svg>
					{{ $formatTime(item['time']) }}
				</div>

				<template v-if="item['effects']">
					<template v-for="(value, resource) in item['effects']">
						<div v-if="value !== 0" class="buildings-effects-row">
							<span :class="'sprite skin_s_'+resource" class="icon" :title="$t('resources.'+resource)"></span>
							<span :class="{ positive: value > 0, negative: value < 0 }">{{ Math.abs(value) }}</span>
						</div>
					</template>
				</template>

				<div v-if="available" class="buldings-active-price">
					<span>Required resources</span>
					<BuildRowPrice :price="item['price']"/>
				</div>

				<div v-if="item['available']" class="building-active-upgrade">
					<div v-if="item['is_max']" class="text-center negative">
						Вы можете построить только {{ item['max'] }} постройку данного типа
					</div>
					<div v-else-if="max > 0" class="buildmax">
						<a @click.prevent="setMax">
							max: <span class="positive">{{ $formatNumber(max) }}</span>
						</a>
						<input type="number" min="0" :max="max" :name="'element[' + item['id'] + ']'" :alt="item['name']" v-model="count" style="width: 80px" maxlength="5" placeholder="0">
					</div>
					<button v-if="!item['is_max'] && max > 0" @click.prevent="buildAction" class="button">
						{{ $t('pages.building.action_build') }}
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
	import { computed, ref } from 'vue';
	import Popup from '../Info/Popup.vue';
	import CloseIcon from '@assets/icons/close.svg?component';
	import { Link, usePage } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';

	const props = defineProps({
		item: {
			type: Object,
		}
	})

	const { tm } = useI18n();
	const count = ref('');
	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);
	const emit = defineEmits(['close', 'build']);

	const level = computed(() => planet.value['units'][props['item']['code']] || 0);

	const max = computed(() => {
		let max = -1;

		Object.keys(tm('resources')).forEach((item) => {
			if (typeof props.item['price'][item] === undefined || typeof planet.value['resources'][item] === 'undefined') {
				return;
			}

			let count = Math.floor(planet.value['resources'][item]['value'] / props.item['price'][item])

			if (max < 0) {
				max = count;
			} else if (max > count) {
				max = count;
			}
		})

		if (props.item['max'] > 0 && props.item['max'] < max) {
			max = props.item['max'];
		}

		return max;
	});

	function setMax () {
		if (count.value === '' || parseInt(count.value) === 0) {
			count.value = max.value;
		} else {
			count.value = '';
		}
	}

	const available = computed(() => {
		return props.item['available'] && !user.value.vacation;
	});

	async function buildAction () {
		emit('build', props.item['id'], count.value);
		count.value = '';
	}
</script>