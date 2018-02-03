<template>
	<div class="col-md-6 col-12">
		<div class="page-building-items-item building" :class="{blocked: !item.allow}">

			<div class="building-info">
				<div class="building-info-img" :style="'background-image: url('+$root.getUrl('assets/images/buildings/planet/'+$parent.page.planet+'_'+(item.i % 4 + 1)+'.png')+')'">
					<a v-on:click="openWindow">
						<img :src="$root.getUrl('assets/images/buildings/item/'+item.i+'.png')" align="top" :alt="item.name" class="tooltip" :data-content="'<center>'+item.name+'</center>'" data-tooltip-width="150">
					</a>

					<div class="building-effects" v-html="item.effects"></div>
				</div>

				<div class="building-info-actions">
					<div class="building-title">
						<a :href="$root.getUrl('info/'+item.i+'/')">
							{{ item.name }}
						</a>
						<span v-if="item.level" class="positive" title="Текущий уровень постройки">
							{{ Format.number(item.level) }}
						</span>
					</div>

					<div class="building-info-info" v-if="item.allow">
						<div class="building-info-time">
							<svg class="icon">
								<use xlink:href="#icon-time"></use>
							</svg>
							{{ Format.time(item.time) }}
						</div>

						<game-page-buildings-build-row-price :price="item.price"></game-page-buildings-build-row-price>

						<div class="building-info-upgrade">
							<a v-if="item.action === 'allow'" :href="$root.getUrl('buildings/index/cmd/insert/building/'+item.i+'/')">
								<svg class="icon">
									<use xlink:href="#icon-constraction"></use>
								</svg>
							</a>
							<a v-else-if="item.action === 'queue'" :href="$root.getUrl('buildings/index/cmd/insert/building/'+item.i+'/')">
								<svg class="icon">
									<use xlink:href="#icon-constraction"></use>
								</svg>
							</a>
						</div>

						<div>
							<span v-if="item.action === 'resources'" class="resNo">нет ресурсов</span>
							<span v-else-if="item.action === 'wait'" class="resNo">{{ item.level ? 'Улучшить' : 'Построить' }}</span>
							<span v-else-if="item.action === 'fields'" class="resNo">нет места</span>
							<span v-else-if="item.action === 'working'" class="resNo">занято</span>
						</div>
					</div>
					<div v-else="" class="building-required">
						<div>Требования</div>
						<div v-html="item['need']"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build-row",
		props: ['item'],
		components: {
			'game-page-buildings-build-row-price': require('./build-row-price.vue')
		},
		methods: {
			openWindow: function () {
				showWindow('', this.$root.getUrl('info/'+this.item.i+'/'), 600)
			}
		}
	}
</script>