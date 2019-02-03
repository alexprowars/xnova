<template>
	<div>{{ time|date('d.m.Y H:i:s') }}</div>
</template>

<script>
	export default {
		name: "overview-clock",
		data ()
		{
			return {
				time: 0,
				timeout: null
			}
		},
		methods: {
			clockUpdate () {
				this.time = this.$root.serverTime();
			},
			clockStop () {
				clearTimeout(this.timeout);
			},
			clockStart () {
				this.timeout = setTimeout(this.clockUpdate, 1000);
			},
		},
		watch: {
			time () {
				this.clockStart();
			}
		},
		mounted ()
		{
			this.clockStop();
			this.clockUpdate();
		},
		destroyed () {
			this.clockStop();
		}
	}
</script>