<template>
	<div class="row">
		<div class="col-6 c">
			{{ item['name'] }} {{ item['level'] }}{{ item['mode'] === 1 ? '. Снос здания' : '' }}
		</div>
		<div class="col-6 k" v-if="index === 0">
			<div v-if="item['time'] > 0" class="z">
				{{ item['time']|time(':', true) }}
				<br>
				<a @click.prevent="cancelItem">Отменить</a>
			</div>
			<div v-else class="z">
				Завершено
				<br>
				<router-link :to="'/buildings/index/planet/'+$store.state.user.planet+'/'">Продолжить</router-link>
			</div>
			<div class="positive">{{ item['end']|date('d.m H:i:s') }}</div>
		</div>
		<div class="col-6 k" v-else>
			<a @click.prevent="deleteItem">Удалить</a>
			<div class="positive">{{ item['end']|date('d.m H:i:s') }}</div>
		</div>
	</div>
</template>

<script>
	import { $post } from 'api'

	export default {
		name: "build-queue-row",
		props: {
			index: Number,
			item: Object
		},
		methods: {
			deleteItem ()
			{
				$.confirm({
					content: 'Удалить <b>'+this.item['name']+' '+this.item['level']+' ур.</b> из очереди?',
					title: 'Очередь построек',
					backgroundDismiss: true,
					buttons: {
						confirm: {
							text: 'удалить',
							action: () =>
							{
								$post('/buildings/index/planet/'+this.$store.state.user.planet+'/', {
									cmd: 'remove',
									listid: this.index
								})
								.then((result) => {
									this.$store.commit('PAGE_LOAD', result)
								})
							}
						},
						cancel: {
							text: 'закрыть'
						}
					}
				})
			},
			cancelItem ()
			{
				$.confirm({
					content: 'Отменить постройку <b>'+this.item['name']+' '+this.item['level']+' ур.</b>?',
					title: 'Очередь построек',
					backgroundDismiss: true,
					buttons: {
						confirm: {
							text: 'отменить',
							action: () =>
							{
								$post('/buildings/index/planet/'+this.$store.state.user.planet+'/', {
									cmd: 'cancel',
									listid: this.index - 1
								})
								.then((result) => {
									this.$store.commit('PAGE_LOAD', result)
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