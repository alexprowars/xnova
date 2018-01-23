<template>
	<tr v-if="time >= 0" class="{{ item.status }}">
		<th width="80">
			<div class="z">{{ Format.time(time, ':', true) }}</div>
			<div class="positive">{{ item.date }}</div>
		</th>
		<th class="text-left" colspan="3">
			<span v-bind:class="[item.status, item.prefix, item.mission]" v-html="item.text"></span>
		</th>
	</tr>
</template>

<script>
	export default {
		name: "overview-fleets-row",
		props: ['item'],
		data: function() {
			return {
				time: 0,
				timeout: null
			}
		},
		methods:
		{
			update()
			{
				if (this.time < 0)
					return;

				this.time = this.item.time - Math.floor(((new Date).getTime() / 1000));
			},
			stop: function() {
				clearTimeout(this.timeout);
			},
			start: function () {
				this.timeout = setTimeout(this.update, 1000);
			}
		},
		watch: {
			time: function() {
				this.start();
			}
		},
		mounted: function()
		{
			this.stop();
			this.update();
		},
		destroyed: function () {
			this.stop();
		}
	}
</script>