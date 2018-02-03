<template>
	<div class="col-md-6 col-12">
		<div class="page-building-items-item building" :class="{blocked: !item.allow}">
			<div class="building-info">
				<div class="building-info-img">
					<a v-on:click="openWindow">
						<img :src="$root.getUrl('assets/images/gebaeude/'+item.i+'.gif')" :alt="item.name" align="top" class="tooltip img-fluid" :data-content="'<center>'+item.name+'</center>'" data-tooltip-width="150">
					</a>
				</div>

				<div class="building-info-actions">
					<div class="building-title">
						<a :href="$root.getUrl('info/'+item.i+'/')">
							{{ item.name }}
						</a>
						<span :class="{positive: item.count > 0, negative: item.count === 0}">{{ Format.number(item.count) }}</span>
					</div>

					<div class="building-info-info" v-if="item.allow">
						<div class="building-info-time">
							<svg class="icon">
								<use xlink:href="#icon-time"></use>
							</svg>
							{{ Format.time(item.time) }}
						</div>

						<div v-html="item.effects"></div>

						<div v-if="item['can']">
							<br>
							<div v-if="item['is_max']">
								<center><font color="red">Вы можете построить только {{ item.max }} постройку данного типа</font></center>
							</div>
							<div v-else="" class="buildmax">
								<a v-on:click.prevent="setMax">
									max: <font color="lime">{{ Format.number(max) }}</font>
								</a>
								<input type="number" min="0" :max="max" :name="'fmenge['+item.i+']'" :alt="item.name" v-model="count" style="width: 80px" maxlength="5" value="" placeholder="0">
							</div>
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
		name: "unit-row",
		props: ['item', 'index'],
		data ()
		{
			return {
				count: ''
			}
		},
		computed: {
			max ()
			{
				let max = -1;

				['metal', 'crystal', 'deuterium', 'energy'].forEach((item) =>
				{
					let count = Math.floor(this.$store.state.resources[item]['current'] / this.item['price'][item]);

					if (max < 0)
						max = count;
					else if (max > count)
						max = count;
				});

				if (this.item['max'] > 0 && this.item['max'] < max)
					max = this.item['max'];

				return max;
			}
		},
		components: {
			'game-page-buildings-build-row-price': require('./build-row-price.vue')
		},
		methods: {
			openWindow ()
			{
				showWindow('', this.$root.getUrl('info/'+this.item['i']+'/'), 600)
			},
			setMax ()
			{
				if (this.count === '' || parseInt(this.count) === 0)
					this.count = this.max;
				else
					this.count = '';
			}
		}
	}
</script>