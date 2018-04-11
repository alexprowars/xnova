<div class="row">

	<div class="e404">Вы попали на несуществующую страницу!</div>

	<div id="gamecontainer" style="margin: 0 auto">
		<canvas id="gameCanvas"></canvas>
	</div>

	<script type="text/javascript">
		var canvas = document.getElementById("gameCanvas");
		canvas.width = 600;
		canvas.height = 500;

		var baseUri = '{{ url.getBaseUri() }}';

		var game = new Game();

		game.initialise(canvas);
		game.start();

		window.addEventListener("keydown", function keydown(e)
		{
			var keycode = e.which || window.event.keycode;

			if (keycode === 37 || keycode === 39 || keycode === 32)
				e.preventDefault();

			game.keyDown(keycode);
		});

		window.addEventListener("keyup", function keydown(e)
		{
			var keycode = e.which || window.event.keycode;
			game.keyUp(keycode);
		});

		function toggleMute()
		{
			game.mute();
			document.getElementById("muteLink").innerText = game.sounds.mute ? "Со звуком" : "Без звука";
		}
	</script>
	<br>
	<div><a id="muteLink" href="#" onclick="toggleMute()">Без звука</a></div>
</div>