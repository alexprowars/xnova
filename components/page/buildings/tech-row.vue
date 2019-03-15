<template>
	<div class="col-md-6 col-12">
		<div class="page-building-items-item building" :class="{blocked: !item.allow}">
			<div class="building-info">
				<div class="building-info-img">
					<popup-link :to="'/info/'+this.item['i']+'/'">
						<img :src="'/images/gebaeude/'+item.i+'.gif'" align="top" :alt="$t('TECH.'+item.i)" class="img-fluid" :v-tooltip="$t('TECH.'+item.i)" data-width="150">
					</popup-link>
				</div>

				<div class="building-info-actions">
					<div class="building-title">
						<nuxt-link :to="'/info/'+item.i+'/'">
							{{ $t('TECH.'+item.i) }}
						</nuxt-link>
						<span v-if="item.level" class="positive" title="Текущий уровень постройки">
							{{ item.level|number }} <span v-if="item.max > 0">из <font color="yellow">{{ item.max|number }}</font></span>
						</span>
					</div>

					<div class="building-info-info" v-if="item.allow">
						<div class="building-info-time">
							<svg class="icon">
								<use xlink:href="/images/symbols.svg#icon-time"></use>
							</svg>
							{{ item.time|time }}
						</div>

						<div v-html="item['effects']"></div>

						<div class="building-info-upgrade">
							<div v-if="typeof item.build === 'object'" class="building-info-upgrade-timer">
								<span v-if="time > 0">
									{{ time|time(':', true) }}&nbsp;<a @click.prevent="cancelAction">Отменить<span v-if="item.build.name.length">на {{ item.build.name }}</span></a>
								</span>
								<nuxt-link v-else :to="'/buildings/research/?chpl='+item.build.id">завершено. продолжить...</nuxt-link>
							</div>
							<div v-else-if="item.max > 0 && item.max <= item.level" class="negative">
								максимальный уровень
							</div>
							<div v-else-if="!hasResources" class="negative text-center">
								нет ресурсов
							</div>
							<a v-else-if="item.build !== true" @click.prevent="addAction" :class="{positive: item.level, negative: item.level === 0}">
								<svg class="icon">
									<use xlink:href="/images/symbols.svg#icon-research"></use>
								</svg>
							</a>
						</div>
					</div>
					<div v-else="" class="building-required">
						<div v-html="item['need']"></div>
					</div>
				</div>
			</div>
			<BuildRowPrice :price="item['price']"></BuildRowPrice>
		</div>
	</div>
</template>

<script>
	import BuildRowPrice from './build-row-price.vue'

	export default {
		name: "tech-row",
		props: {
			item: {
				type: Object
			}
		},
		components: {
			BuildRowPrice
		},
		data () {
			return {
				time: 0,
				timeout: null
			}
		},
		computed: {
			resources () {
				return this.$store.state.resources || {};
			},
			hasResources ()
			{
				let allow = true;

				let resources = Object.keys(this.$t('RESOURCES'));

				resources.forEach((res) =>
				{
					if (typeof this.item.price[res] !== 'undefined' && this.item.price[res] > 0
						&& this.resources[res] && this.resources[res].current < this.item.price[res])
						allow = false;
				});

				return allow;
			}
		},
		methods: {
			update ()
			{
				if (typeof this.item['build'] !== 'object' || this.time < 0)
				{
					this.time = 0;
					this.stop();
					return;
				}

				this.time = this.item['build']['time'] - this.$store.getters.getServerTime();
			},
			stop () {
				clearTimeout(this.timeout);
			},
			start () {
				this.timeout = setTimeout(this.update, 1000);
			},
			addAction ()
			{
				this.$post('/buildings/research/', {
					cmd: 'search',
					tech: this.item['i']
				})
				.then((result) => {
					this.$store.commit('PAGE_LOAD', result);
				})
			},
			cancelAction ()
			{
				this.$dialog
					.confirm('Отменить изучение <b>'+this.$t('TECH.'+this.item['i'])+' '+this.item['level']+' ур.</b>?', {
						okText: 'Отменить',
						cancelText: 'Закрыть',
					})
					.then(() =>
					{
						this.$post('/buildings/research/', {
							cmd: 'cancel',
							tech: this.item['i']
						})
						.then((result) => {
							this.$store.commit('PAGE_LOAD', result);
						})
					})
			}
		},
		watch: {
			time (v)
			{
				if (v > 0)
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