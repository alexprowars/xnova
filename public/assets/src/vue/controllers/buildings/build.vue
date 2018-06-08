<template>
	<div class="page-building page-building-build">
		<div class="block">
			<div class="title">
				<div class="row">
					<div class="col-12 col-sm-6">
						Занято полей
						<span class="positive">{{ page['fields_current'] }}</span> из <span class="negative">{{ page['fields_max'] }}</span>
					</div>
					<div class="text-sm-right col-12 col-sm-6">
						Осталось
						<span class="positive">{{ fields_empty }}</span>
						свободн{{ page.fields_empty|morph(['ое', 'ых', 'ых']) }}
						пол{{ page.fields_empty|morph(['е', 'я', 'ей']) }}
					</div>
				</div>
			</div>

			<div class="page-building-build-queue" v-if="page.queue.length">
				<div class="table">
					<div v-for="(item, index) in page.queue" class="row">
						<div class="col-6 c">
							{{ item.name }} {{ item.level }}{{ item.mode === 1 ? '. Снос здания' : '' }}
						</div>
						<div class="col-6 k" v-if="index === 0">
							<div v-if="item.time > 0" class="z">
								{{ item.time|time(':', true) }}
								<br>
								<a v-if="cheat <= 0" :href="$root.getUrl('buildings/index/listid/'+(index + 1)+'/cmd/cancel/planet/'+pl+'/')">Отменить</a>
							</div>
							<div v-else class="z">
								Завершено
								<br>
								<a :href="$root.getUrl('buildings/index/planet/'+$store.state.user.planet+'/')">Продолжить</a>
							</div>
							<div class="positive">{{ item.end|date('d.m H:i:s') }}</div>
						</div>
						<div class="col-6 k" v-else>
							<a :href="$root.getUrl('buildings/index/listid/'+index+'/cmd/remove/planet/'+$store.state.user.planet+'/')">Удалить</a>
							<div class="positive">{{ item.end|date('d.m H:i:s') }}</div>
						</div>
					</div>
				</div>
			</div>

			<div class="content page-building-items">
				<div class="row">
					<game-page-buildings-build-item v-for="item in page.items" :item="item"></game-page-buildings-build-item>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build",
		data () {
			return {
				cheat: 3,
				timeout: null
			}
		},
		computed: {
			page () {
				return this.$store.state.page;
			},
			fields_empty () {
				return this.page['fields_max'] - this.page['fields_current'] - this.page.queue.length;
			}
		},
		components: {
			'game-page-buildings-build-item': require('./build-row.vue')
		},
		mounted () {
			this.init();
		},
		methods:
		{
			init ()
			{
				clearTimeout(this.timeout);

				this.cheat = 3;

				if (this.page.queue.length > 0)
					this.timeout = setTimeout(this.timer, 1000);
			},
			timer ()
			{
				if (this.cheat > 0)
					this.cheat -= 1;

				this.page.queue[0].time -= 1;

				if (this.page.queue[0].time <= 0)
				{
					this.timeout = setTimeout(() => {
						this.$root.load(this.$root.getUrl('buildings/index/planet/'+this.$store.state.user.planet+'/'));
					}, 5000);
				}
				else
					this.timeout = setTimeout(this.timer, 1000);
			}
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