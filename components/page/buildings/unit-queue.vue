<template>
	<div class="block page-building-unit-queue">
		<div class="title">Текущее производство</div>
		<div class="content border-0">
			<div class="table">
				<div v-for="item in queue" class="row">
					<div class="col-6 text-left k">
						<span class="positive">{{ item.count }}</span> {{ item.name }}
					</div>
					<div class="col-6 text-right k border-left-0">
						{{ (item.end - $store.getters.getServerTime()) | time }}
					</div>
				</div>
				<div class="row">
					<div class="col-12 c">
						Оставшееся время {{ left_time | time }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "unit-queue",
		props: {
			queue: {
				type: Array,
				default: () => {
					return []
				}
			}
		},
		data () {
			return {
				left_time: 0,
				timeout: null
			}
		},
		methods: {
			start () {
				this.timeout = setTimeout(this.update, 1000);
			},
			stop () {
				clearTimeout(this.timeout);
			},
			update ()
			{
				if (this.queue.length === 0)
					return;

				let last = this.queue[this.queue.length - 1];
				this.left_time = last.end - this.$store.getters.getServerTime()

				let first = this.queue[0];

				if (first.end <= this.$store.getters.getServerTime())
					this.queue.splice(0, 1);
				else
				{
					let cnt = Math.ceil((first.end - this.$store.getters.getServerTime()) / first.time);

					if (cnt !== this.queue[0]['count'])
						this.queue[0]['count'] = cnt;
				}
			}
		},
		watch: {
			left_time () {
				this.start();
			}
		},
		updated ()
		{
			this.stop();
			this.update();
			this.start();
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