<template>
	<div class="block">
		<div class="title">Снос здания "{{ $t('TECH.'+item) }}" уровень {{ data['level'] }}</div>
		<div class="content border-0">
			<div class="table">
				<div class="row">
					<div class="col th">
						<build-row-price :price="data['resources']"></build-row-price>
					</div>
				</div>
				<div class="row">
					<div class="col th">
						Время сноса: {{ data['time']|time }}

						<button @click.prevent="destroyAction">Снести</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import BuildRowPrice from '~/components/page/buildings/build-row-price.vue'

	export default {
		name: "info-destroy",
		components: {
			BuildRowPrice
		},
		props: {
			data: Object,
			item: Number
		},
		methods: {
			destroyAction ()
			{
				this.$dialog
					.confirm({
						body: 'Снести постройку <b>'+this.$t('TECH.'+this.item)+' '+this.data['level']+' ур.</b>?',
					}, {
						okText: 'Снести',
						cancelText: 'Закрыть',
					})
					.then(() =>
					{
						this.$post('/buildings/', {
							cmd: 'destroy',
							building: this.item
						})
						.then((result) => {
							this.$store.commit('PAGE_LOAD', result);
						})
					})
			}
		}
	}
</script>