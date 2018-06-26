<template>
	<div class="col-md-6 col-12">
		<div class="page-building-items-item building" :class="{blocked: !item.allow}">

			<div class="building-info">
				<div class="building-info-img" :style="'background-image: url('+$root.getUrl('assets/images/buildings/planet/'+$parent.page.planet+'_'+(item.i % 4 + 1)+'.png')+')'">
					<popup-link :to="'/info/'+this.item['i']+'/'">
						<img :src="$root.getUrl('assets/images/buildings/item/'+item.i+'.png')" align="top" :alt="$root.getLang('TECH', item.i)" class="tooltip" :data-content="$root.getLang('TECH', item.i)" data-width="150">
					</popup-link>
					<div class="building-effects" v-html="item.effects"></div>
				</div>

				<div class="building-info-actions">
					<div class="building-title">
						<router-link :to="'/info/'+item.i+'/'">
							{{ $root.getLang('TECH', item.i) }}
						</router-link>
						<span v-if="item.level" class="positive" title="Текущий уровень постройки">
							{{ item.level|number }}
						</span>
					</div>

					<div class="building-info-info" v-if="item.allow">
						<div class="building-info-time">
							<svg class="icon">
								<use xlink:href="#icon-time"></use>
							</svg>
							{{ item.time|time }}
						</div>

						<div class="building-info-upgrade">
							<div v-if="$parent.fields_empty <= 0" class="negative">
								нет места
							</div>
							<a v-else-if="$parent.page['queue_max'] > 1 && $parent.page['queue'].length > 0" @click.prevent="addAction">
								<svg class="icon">
									<use xlink:href="#icon-constraction"></use>
								</svg>
							</a>
							<div v-else-if="!hasResources" class="negative text-center">
								нет ресурсов
							</div>
							<div v-else-if="$parent.page['queue_max'] <= $parent.page['queue'].length" class="negative">
								очередь заполнена
							</div>
							<a v-else-if="$parent.page['queue'].length === 0" @click.prevent="addAction">
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

			<build-row-price :price="item.price"></build-row-price>
		</div>
	</div>
</template>

<script>
	import BuildRowPrice from './build-row-price.vue'
	import { $post } from 'api'

	export default {
		name: "build-row",
		props: ['item'],
		components: {
			BuildRowPrice
		},
		computed: {
			resources () {
				return this.$store.state.resources;
			},
			hasResources ()
			{
				let allow = true;

				let resources = Object.keys(this.$root.getLang('RESOURCES'));

				resources.forEach((res) =>
				{
					if (typeof this.item.price[res] !== 'undefined' && this.item.price[res] > 0)
					{
						if (res === 'energy')
						{
							if (this.resources[res].max < this.item.price[res])
								allow = false;
						}
						else if (this.resources[res].current < this.item.price[res])
							allow = false;
					}
				});

				return allow;
			}
		},
		methods: {
			addAction ()
			{
				$post('/buildings/', {
					cmd: 'insert',
					building: this.item['i']
				})
				.then((result) => {
					this.$store.commit('PAGE_LOAD', result)
				})
			}
		}
	}
</script>