<template>
	<div class="col-md-6 col-12">
		<div class="page-building-items-item building" :class="{blocked: !item.allow}">
			<div class="building-info">
				<div class="building-info-img">
					<a v-on:click="openWindow">
						<img :src="$root.getUrl('assets/images/gebaeude/'+item.i+'.gif')" align="top" :alt="$root.getLang('TECH', item.i)" class="tooltip img-fluid" :data-content="$root.getLang('TECH', item.i)" data-width="150">
					</a>
				</div>

				<div class="building-info-actions">
					<div class="building-title">
						<a :href="$root.getUrl('info/'+item.i+'/')">
							{{ $root.getLang('TECH', item.i) }}
						</a>
						<span v-if="item.level" class="positive" title="Текущий уровень постройки">
							{{ Format.number(item.level) }} <span v-if="item.max > 0">из <font color="yellow">{{ Format.number(item.max) }}</font></span>
						</span>
					</div>

					<div class="building-info-info" v-if="item.allow">
						<div class="building-info-time">
							<svg class="icon">
								<use xlink:href="#icon-time"></use>
							</svg>
							{{ Format.time(item.time) }}
						</div>

						<div v-html="item.effects"></div>

						<div class="building-info-upgrade">
							<div v-if="typeof item.build === 'object'" class="building-info-upgrade-timer">
								<span v-if="time > 0">
									{{ Format.time(time, ':', true) }}&nbsp;<a :href="$root.getUrl('buildings/research/cmd/cancel/tech/'+item.i+'/')">Отменить<span v-if="item.build.name.length">на {{ item.build.name }}</span></a>
								</span>
								<a v-else :href="$root.getUrl('buildings/research/?chpl='+item.build.id+'')">завершено. продолжить...</a>
							</div>
							<div v-else-if="item.max > 0 && item.max <= item.level" class="negative">
								максимальный уровень
							</div>
							<div v-else-if="!hasResources" class="negative text-center">
								нет ресурсов
							</div>
							<a v-else-if="item.build !== true" :href="$root.getUrl('buildings/research/cmd/search/tech/'+item.i+'/')" :class="{positive: item.level, negative: item.level === 0}">
								<svg class="icon">
									<use xlink:href="#icon-research"></use>
								</svg>
							</a>
						</div>
					</div>
					<div v-else="" class="building-required">
						<div v-html="item['need']"></div>
					</div>
				</div>
			</div>
			<game-page-buildings-build-row-price :price="item.price"></game-page-buildings-build-row-price>
		</div>
	</div>
</template>

<script>
	export default {
		name: "tech-row",
		props: ['item'],
		components: {
			'game-page-buildings-build-row-price': require('./build-row-price.vue')
		},
		data () {
			return {
				time: 0,
				timeout: null
			}
		},
		computed: {
			resources () {
				return this.$store.state.resources;
			},
			hasResources ()
			{
				let allow = true;

				['metal', 'crystal', 'deuterium', 'energy'].forEach((res) =>
				{
					if (typeof this.item.price[res] !== 'undefined' && this.item.price[res] > 0 && this.resources[res].current < this.item.price[res])
						allow = false;
				});

				return allow;
			}
		},
		methods: {
			openWindow () {
				showWindow('', this.$root.getUrl('info/'+this.item['i']+'/'), 600)
			},
			update ()
			{
				if (typeof this.item['build'] !== 'object' || this.time < 0)
				{
					this.stop();
					return;
				}

				this.time = this.item['build']['time'] - this.$root.serverTime();
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
			},
			'item.build' (v)
			{
				if (typeof v === 'object')
				{
					this.stop();
					this.update();
					this.start();
				}
			}
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