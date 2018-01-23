<template>
	<div class="col-md-6 col-12">
		<div class="viewport buildings" :class="{shadow: !item.allow}">
			<div v-if="item.allow === false" class="notAvailable tooltip" v-on:click="openWindow">
				<div class="tooltip-content" v-if="item.need">
					Требования:
					<br>
					<div v-html="item.need"></div>
				</div>
				<span>недоступно</span>
			</div>
			<div class="img">
				<a v-on:click="openWindow">
					<img v-bind:src="$root.getUrl('assets/images/gebaeude/'+item.i+'.gif')" :alt="item.name" align="top" width="120" height=120 class="tooltip" :data-content="'<center>'+item.name+'</center>'" data-tooltip-width="150">
				</a>

				<div class="overContent">
					<game-page-buildings-build-row-price v-bind:price="item.price"></game-page-buildings-build-row-price>
				</div>
			</div>
			<div class="title">
				<a v-bind:href="$root.getUrl('info/'+item.i+'/')">
					{{ item.name }}
				</a>
				(<span :class="{positive: item.count > 0, negative: item.count === 0}">{{ Format.number(item.count) }}</span>)
			</div>
			<div class="actions">
				<div v-if="item.allow">
					<div>Время: {{ Format.time(item.time) }}</div>
					<div v-html="item.effects"></div>

					<div v-if="item.can">
						<br>
						<div v-if="item.is_max">
							<center><font color="red">Вы можете построить только {{ item.max }} постройку данного типа</font></center>
						</div>
						<div v-else>
							<a v-on:click.prevent="max">
								Максимум: <font color="lime">{{ item.max }}</font>
							</a>
							<div class="buildmax">
								<input type="number" :name="'fmenge['+item.i+']'" :alt="item.name" v-model="count" style="max-width: 80px" maxlength="5" value="" placeholder="0">
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
		components: {
			'game-page-buildings-build-row-price': require('./build-row-price.vue')
		},
		methods: {
			openWindow ()
			{
				showWindow('', this.$root.getUrl('info/'+this.item['i']+'/'), 600)
			},
			max ()
			{
				if (this.count === '' || parseInt(this.count) === 0)
					this.count = this.item.max;
				else
					this.count = '';
			}
		}
	}
</script>