<template>
	<div class="page-building-build-queue">
		<div class="table">
			<BuildQueueRow v-for="(item, index) in queue" :key="index" :index="index" :item="item"></BuildQueueRow>
		</div>
	</div>
</template>

<script>
	import BuildQueueRow from './build-queue-row.vue'

	export default {
		name: "build-queue",
		props: {
			queue: Array
		},
		components: {
			BuildQueueRow
		},
		data () {
			return {
				timeout: null
			}
		},
		mounted () {
			this.init();
		},
		methods:
		{
			init ()
			{
				clearTimeout(this.timeout);

				if (this.queue.length > 0)
					this.timeout = setTimeout(this.timer, 1000);
			},
			timer ()
			{
				this.queue[0]['time'] -= 1;

				if (this.queue[0]['time'] <= 0)
				{
					this.timeout = setTimeout(() => {
						this.$router.push('/buildings/?planet='+this.$store.state.user.planet);
					}, 5000);
				}
				else
					this.timeout = setTimeout(this.timer, 1000);
			},
		},
		watch: {
			page () {
				this.init();
			}
		},
		destroyed () {
			clearTimeout(this.timeout);
		}
	}
</script>