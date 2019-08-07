<template>
	<div class="row">

		<div v-if="error.statusCode === 404" class="e404">Вы попали на несуществующую страницу!</div>
		<div v-else>{{ error['message'] }}</div>

		<div id="gamecontainer" style="margin: 0 auto">
			<canvas id="gameCanvas"></canvas>
		</div>

		<script type="text/javascript">

		</script>
		<br>
		<div><a href="#" @click.prevent="toggleMute">{{ sound }}</a></div>
	</div>
</template>

<script>
	import Game from '~/utils/spaceinvaders'

	export default {
		props: {
			error: Object
		},
		data () {
			return {
				game: null
			}
		},
		computed: {
			sound () {
				return this.game && this.game.sounds.mute ? "Со звуком" : "Без звука"
			}
		},
		mounted ()
		{
			let canvas = document.getElementById("gameCanvas");
			canvas.width = 600;
			canvas.height = 500;

			let game = new Game();

			game.initialise(canvas);
			game.start();

			this.game = game

			window.addEventListener("keydown", (e) =>
			{
				let keycode = e.keyCode;

				if (keycode === 37 || keycode === 39 || keycode === 32)
					e.preventDefault();

				this.game.keyDown(keycode);
			});

			window.addEventListener("keyup", (e) => {
				this.game.keyUp(e.keyCode);
			});
		},
		methods: {
			toggleMute () {
				this.game.mute();
			}
		}
	}
</script>