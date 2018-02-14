<template>
	<div class="page-building-unit-queue table">
		<div class="row">
			<div class="col-12 th">
				Текущее производство:
			</div>
		</div>
		<div v-for="item in queue" class="row">
			<div class="col-6 text-left k">
				<span class="positive">{{ item.count }}</span> {{ item.name }}
			</div>
			<div class="col-6 text-right k border-left-0">
				{{ Format.time(item.end - $root.serverTime()) }}
			</div>
		</div>
		<div class="row">
			<div class="col-12 c">
				Оставшееся время {{ Format.time(left_time) }}
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "unit-queue",
		props: ['queue'],
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
				let last = this.queue[this.queue.length - 1];
				this.left_time = last.end - this.$root.serverTime()

				let first = this.queue[0];

				if (first.end <= this.$root.serverTime())
					this.queue.splice(0, 1);
				else
				{
					let cnt = Math.ceil((first.end - this.$root.serverTime()) / first.time);

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