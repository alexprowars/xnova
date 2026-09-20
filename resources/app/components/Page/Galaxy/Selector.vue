<template>
	<form ref="form" class="page-galaxy-select" @submit.prevent="change">
		<div>
			<label for="galaxy-coordinate">{{ $t('pages.galaxy.selector.galaxy') }}</label>
			<div class="galaxy-stepper">
				<button type="button" class="button is-secondary icon-button" :disabled="galaxy === 1" :aria-label="$t('pages.galaxy.selector.previous_galaxy')" @click="changeByDirection('galaxyLeft')"><GalaxyIcon type="left"/></button>
				<input id="galaxy-coordinate" name="galaxy" v-model.number="inputGalaxy" min="1" :max="galaxyMax" type="number" required>
				<button type="button" class="button is-secondary icon-button" :disabled="galaxy >= galaxyMax" :aria-label="$t('pages.galaxy.selector.next_galaxy')" @click="changeByDirection('galaxyRight')"><GalaxyIcon type="right"/></button>
			</div>
		</div>
		<div>
			<label for="system-coordinate">{{ $t('pages.galaxy.selector.system') }}</label>
			<div class="galaxy-stepper">
				<button type="button" class="button is-secondary icon-button" :disabled="system === 1" :aria-label="$t('pages.galaxy.selector.previous_system')" @click="changeByDirection('systemLeft')"><GalaxyIcon type="left"/></button>
				<input id="system-coordinate" name="system" v-model.number="inputSystem" min="1" :max="systemMax" type="number" required>
				<button type="button" class="button is-secondary icon-button" :disabled="system >= systemMax" :aria-label="$t('pages.galaxy.selector.next_system')" @click="changeByDirection('systemRight')"><GalaxyIcon type="right"/></button>
			</div>
		</div>
		<div class="galaxy-shortcuts-field">
			<label for="galaxy-shortcut">{{ $t('pages.galaxy.selector.shortcuts') }}</label>
			<GalaxySelectorShortcut :items="shortcuts" :galaxy="galaxy" :system="system" v-model="shortcut"/>
		</div>
		<button type="submit" class="button galaxy-go">{{ $t('pages.galaxy.selector.go') }}<GalaxyIcon type="right"/></button>
	</form>
</template>

<script setup>
	import GalaxySelectorShortcut from './SelectorShortcut.vue';
	import GalaxyIcon from './GalaxyIcon.vue';
	import { ref, watch } from 'vue';

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

	const inputGalaxy = ref(props.galaxy);
	const inputSystem = ref(props.system);
	const shortcut = ref(null);

	resetShortcut();

	watch(() => [props.galaxy, props.system], () => {
		inputGalaxy.value = props.galaxy;
		inputSystem.value = props.system;
		resetShortcut();
	});
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