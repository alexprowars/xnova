<template>
	<div class="col-md-6 col-12">
		<div class="viewport buildings" :class="{shadow: !item.allow}">
			<div v-if="item.allow === false" class="notAvailable tooltip" v-on:click="openWindow">
				<div class="tooltip-content" v-if="item['need']">
					Требования:
					<br>
					<div v-html="item['need']"></div>
				</div>
				<span>недоступно</span>
			</div>
			<div class="img">
				<a v-on:click="openWindow">
					<img :src="$root.getUrl('assets/images/gebaeude/'+item.i+'.gif')" :alt="item.name" align="top" width="120" height=120 class="tooltip" :data-content="'<center>'+item.name+'</center>'" data-tooltip-width="150">
				</a>

				<div class="overContent">
					<game-page-buildings-build-row-price :price="item.price"></game-page-buildings-build-row-price>
				</div>
			</div>
			<div class="title">
				<a :href="$root.getUrl('info/'+item.i+'/')">
					{{ item.name }}
				</a>
				(<span :class="{positive: item.count > 0, negative: item.count === 0}">{{ Format.number(item.count) }}</span>)
			</div>
			<div class="actions">
				<div v-if="item.allow">
					<div>Время: {{ Format.time(item.time) }}</div>
					<div v-html="item.effects"></div>

					<div v-if="item['can']">
						<br>
						<div v-if="item['is_max']">
							<center><font color="red">Вы можете построить только {{ item.max }} постройку данного типа</font></center>
						</div>
						<div v-else>
							<a v-on:click.prevent="setMax">
								Максимум: <font color="lime">{{ max }}</font>
							</a>
							<div class="buildmax">
								<input type="number" min="0" :max="max" :name="'fmenge['+item.i+']'" :alt="item.name" v-model="count" style="width: 80px" maxlength="5" value="" placeholder="0">
							</div>
						</div>
					</div>
				</div>
			</div>
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
					this.count = this.item.max;
				else
					this.count = '';
			}
		}
	}
</script>