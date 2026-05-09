<template>
	<div>
		<div id="gamecontainer" style="margin: 0 auto">
			<canvas ref="gameRef" id="gameCanvas"></canvas>
		</div>
		<br>
		<div><a href="#" @click.prevent="toggleMute">{{ sound }}</a></div>
	</div>
</template>

<script setup>
	import { computed, onMounted, ref } from 'vue';
	import Game from '~/utils/spaceinvaders';

	let game = null;
	let gameRef = ref(null);

	const sound = computed(() => {
		return game && game.sounds.mute ? 'Со звуком' : 'Без звука';
	});

	onMounted(() => {
		let canvas = gameRef.value;
		canvas.width = 600;
		canvas.height = 500;

		game = new Game();
		game.initialise(canvas);
		game.start();

		window.addEventListener('keydown', (e) => {
			let keycode = e.keyCode;

			if (keycode === 37 || keycode === 39 || keycode === 32) {
				e.preventDefault();
			}

			game.keyDown(keycode);
		});

		window.addEventListener('keyup', (e) => {
			game.keyUp(e.keyCode);
		});
	});

	function toggleMute () {
		game.mute();
	}
</script>