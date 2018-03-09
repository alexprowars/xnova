<template>
	<div class="col-md-6 col-12">
		<div class="page-building-items-item building" :class="{blocked: !item.allow}">

			<div class="building-info">
				<div class="building-info-img" :style="'background-image: url('+$root.getUrl('assets/images/buildings/planet/'+$parent.page.planet+'_'+(item.i % 4 + 1)+'.png')+')'">
					<a @click.prevent="openWindow">
						<img :src="$root.getUrl('assets/images/buildings/item/'+item.i+'.png')" align="top" :alt="$root.getLang('TECH', item.i)" class="tooltip" :data-content="$root.getLang('TECH', item.i)" data-width="150">
					</a>
					<div class="building-effects" v-html="item.effects"></div>
				</div>

				<div class="building-info-actions">
					<div class="building-title">
						<a :href="$root.getUrl('info/'+item.i+'/')">
							{{ $root.getLang('TECH', item.i) }}
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

						<div class="building-info-upgrade">
							<div v-if="$parent.fields_empty <= 0" class="negative">
								нет места
							</div>
							<div v-else-if="!hasResources" class="negative text-center">
								нет ресурсов
							</div>
							<div v-else-if="$parent.page['queue_max'] <= $parent.page['queue'].length" class="negative">
								очередь заполнена
							</div>
							<a v-else-if="$parent.page['queue'].length === 0" :href="$root.getUrl('buildings/index/cmd/insert/building/'+item.i+'/')">
								<svg class="icon">
									<use xlink:href="#icon-constraction"></use>
								</svg>
							</a>
							<a v-else-if="$parent.page['queue_max'] > 1" :href="$root.getUrl('buildings/index/cmd/insert/building/'+item.i+'/')">
								<svg class="icon">
									<use xlink:href="#icon-constraction"></use>
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
		name: "build-row",
		props: ['item'],
		components: {
			'game-page-buildings-build-row-price': require('./build-row-price.vue'),
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
			openWindow: function () {
				showWindow('', this.$root.getUrl('info/'+this.item.i+'/'), 600)
			}
		}
	}
</script>