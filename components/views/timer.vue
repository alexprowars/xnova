<template>
	<div>{{ time | time(delimiter, true) }}</div>
</template>

<script>
	export default {
		name: "timer",
		props: {
			value: {
				type: Number,
				default: 0
			},
			delimiter: {
				type: String,
				default: ':'
			}
		},
		data () {
			return {
				time: -1,
				timeout: null
			}
		},
		methods: {
			update ()
			{
				if (this.time < 0)
					return;

				this.time = this.value - this.$store.getters.getServerTime();
			},
			stop () {
				clearTimeout(this.timeout);
			},
			start () {
				this.timeout = setTimeout(this.update, 1000);
			}
		},
		watch: {
			time () {
				this.start();
			}
		},
		created () {
			this.time = this.value - this.$store.getters.getServerTime()
		},
		mounted ()
		{
			this.stop();
			this.update();
		},
		destroyed () {
			this.stop();
		}
	}
</script>