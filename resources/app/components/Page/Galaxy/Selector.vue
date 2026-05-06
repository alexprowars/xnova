<template>
	<form ref="form" class="page-galaxy-select" @submit.prevent="change">
		<div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
			<div class="col-span-2 sm:col-span-1 sm:order-2">
				<GalaxySelectorShortcut :items="shortcuts" :galaxy="galaxy" :system="system" v-model="shortcut"/>
			</div>
			<div class="sm:order-1 text-center">
				<div class="block-table inline-block">
					<div class="flex">
						<div class="w-full c">
							{{ $t('pages.galaxy.selector.galaxy') }}
						</div>
					</div>
					<div class="flex">
						<div class="th middle">
							<button :disabled="galaxy === 1" @click.prevent="changeByDirection('galaxyLeft')">&lt;-</button>
						</div>
						<div class="th middle">
							<input name="galaxy" v-model.number="inputGalaxy" maxlength="3" tabindex="1" min="1" type="number">
						</div>
						<div class="th middle">
							<button :disabled="galaxy >= galaxyMax" @click.prevent="changeByDirection('galaxyRight')">-&gt;</button>
						</div>
					</div>
				</div>
			</div>
			<div class="sm:order-3 text-center">
				<div class="block-table inline-block">
					<div class="flex">
						<div class="w-full c">
							{{ $t('pages.galaxy.selector.system') }}
						</div>
					</div>
					<div class="flex">
						<div class="th middle">
							<button :disabled="system === 1" @click.prevent="changeByDirection('systemLeft')">&lt;-</button>
						</div>
						<div class="th middle">
							<input name="system" v-model.number="inputSystem" maxlength="3" tabindex="2" min="1" type="number">
						</div>
						<div class="th middle">
							<button :disabled="system >= systemMax" @click.prevent="changeByDirection('systemRight')">-&gt;</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</template>

<script setup>
	import GalaxySelectorShortcut from './SelectorShortcut.vue'
	import { computed, ref, watch } from 'vue';

	const props = defineProps({
		galaxy: {
			type: Number,
			default: 1
		},
		galaxyMax: {
			type: Number,
			default: 1
		},
		system: {
			type: Number,
			default: 1
		},
		systemMax: {
			type: Number,
			default: 1
		},
		shortcuts: {
			type: Array,
			default: () => []
		}
	});

	const emit = defineEmits(['change']);

	const inputGalaxy = computed(() => props.galaxy);
	const inputSystem = computed(() => props.system);
	const shortcut = ref(null);

	resetShortcut();

	watch(() => [props.galaxy, props.system], () => resetShortcut());
	watch(shortcut, (value) => shortcutChange(value));

	function shortcutChange(val) {
		if (!val) {
			return;
		}

		emit('change', {
			galaxy: val.galaxy, system: val.system,
		});
	}

	function resetShortcut() {
		shortcut.value = props.shortcuts.find((item) =>
			item['galaxy'] === props.galaxy && item['system'] === props.system
		) || null;
	}

	function changeByDirection(direction) {
		let coords = {
			galaxy: props.galaxy,
			system: props.system,
		}

		if (direction === 'galaxyLeft') {
			coords.galaxy -= 1;
		} else if (direction === 'galaxyRight') {
			coords.galaxy += 1;
		}

		if (direction === 'systemLeft') {
			coords.system -= 1;
		} else if (direction === 'systemRight') {
			coords.system += 1;
		}

		emit('change', coords);
	}

	function change() {
		emit('change', {
			galaxy: inputGalaxy.value,
			system: inputSystem.value,
		});
	}
</script>