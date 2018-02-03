<template>
	<div class="col-md-6 col-12">
		<div class="page-building-items-item building" :class="{blocked: !item.allow}">
			<div class="building-info">
				<div class="building-info-img">
					<a v-on:click="openWindow">
						<img :src="$root.getUrl('assets/images/gebaeude/'+item.i+'.gif')" align="top" class="tooltip img-fluid" :data-content="'<center>'+item.name+'</center>'" data-tooltip-width="150">
					</a>
				</div>

				<div class="building-info-actions">
					<div class="building-title">
						<a :href="$root.getUrl('info/'+item.i+'/')">
							{{ item.name }}
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
					</div>
					<div v-else="" class="building-required">
						<div v-html="item['need']"></div>
					</div>

					<div class="building-info-upgrade">
						<a v-if="item.action === 'allow'" :href="$root.getUrl('buildings/research/cmd/search/tech/'+item.i+'/')" :class="{positive: item.level, negative: item.level === 0}">
							<svg class="icon">
								<use xlink:href="#icon-research"></use>
							</svg>
						</a>
						<span v-else-if="item.action === 'max'" class="negative">максимальный уровень</span>
						<span v-else-if="item.action === 'working'" :class="{positive: item.level, negative: item.level === 0}">{{ item.level ? 'Улучшить' : 'Исследовать' }}</span>
						<span v-else-if="item.action === 'resources'" class="resNo">нет ресурсов</span>
						<div v-else-if="item.action === 'progress'" class="building-info-upgrade-timer">
							<span v-if="time > 0">
								{{ Format.time(time, ':', true) }}&nbsp;<a :href="$root.getUrl('buildings/research/cmd/cancel/tech/'+item.i+'/')">Отменить<span v-if="item.build.name.length">на {{ item.build.name }}</span></a>
							</span>
							<a v-else :href="$root.getUrl('buildings/research/?chpl='+item.build.id+'')">завершено. продолжить...</a>
						</div>
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
		methods: {
			openWindow () {
				showWindow('', this.$root.getUrl('info/'+this.item['i']+'/'), 600)
			},
			update ()
			{
				if (this.time < 0 || typeof this.item['build'] === 'undefined')
					return;

				this.time = this.item.build.time - this.$root.serverTime();
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