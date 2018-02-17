<template>
	<div v-if="time >= 0" class="row overview-fleets-row">
		<div class="col-2 th">
			<div class="z">{{ Format.time(time, ':', true) }}</div>
			<div class="positive">{{ item['date'] }}</div>
		</div>
		<div class="col-10 th text-left" :class="[item['status'], item['prefix'], item['mission']]" v-html="item['text']"></div>
	</div>
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

				this.time = this.item.time - this.$root.serverTime();
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