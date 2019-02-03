<template>
	<div class="block">
		<div class="title">Снос здания "{{ $root.getLang('TECH', item) }}" уровень {{ data['level'] }}</div>
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
	import BuildRowPrice from '../buildings/build-row-price.vue'
	import { $post } from 'api'

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
				$.confirm({
					content: 'Снести постройку <b>'+this.$root.getLang('TECH', this.item)+' '+this.data['level']+' ур.</b>?',
					title: 'Очередь построек',
					backgroundDismiss: true,
					buttons: {
						confirm: {
							text: 'снести',
							action: () =>
							{
								$post('/buildings/', {
									cmd: 'destroy',
									building: this.item
								})
								.then((result) => {
									this.$store.commit('PAGE_LOAD', result);
									this.$router.replace(result['url'])
								})
							}
						},
						cancel: {
							text: 'закрыть'
						}
					}
				})
			}
		}
	}
</script>