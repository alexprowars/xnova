<template>
	<form name="Atr" action="">
		<div class="separator"></div>
		<table width="100%">
			<tr>
				<th>
					Текущее производство: <div class="z"></div>
				</th>
			</tr>
			<tr>
				<th class="k">
					<div v-for="item in queue" class="row">
						<div class="col-6 text-left">
							<span class="positive">{{ item.count }}</span> {{ item.name }}
						</div>
						<div class="col-6 text-right">
							{{ Format.time(item.end - $root.serverTime()) }}
						</div>
					</div>
				</th>
			</tr>
			<tr>
				<td class="c">Оставшееся время {{ Format.time(left_time) }}</td>
			</tr>
		</table>
		<div class="separator"></div>
	</form>
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