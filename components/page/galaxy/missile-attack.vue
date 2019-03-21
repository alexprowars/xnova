<template>
	<form action="" method="post" @submit.prevent="send">
		<div class="table">
			<div class="row">
				<div class="col c">
					Начать ракетную атаку на [{{ page['galaxy'] }}:{{ page['system'] }}:{{ planet }}]
				</div>
			</div>
			<div class="row">
				<div class="col th">
					Количество ракет ({{ page['user']['interplanetary_misil'] }}):
					<input type="number" style="width:25%" min="1" :max="page['user']['interplanetary_misil']" v-model.number="count">
				</div>
				<div class="col th">
					Цель:
					<select name="target" v-model="target">
						<option value="all">Вся оборона</option>
						<option value="0">{{ $t('TECH.401') }}</option>
						<option value="1">{{ $t('TECH.402') }}</option>
						<option value="2">{{ $t('TECH.403') }}</option>
						<option value="3">{{ $t('TECH.404') }}</option>
						<option value="4">{{ $t('TECH.405') }}</option>
						<option value="5">{{ $t('TECH.406') }}</option>
						<option value="6">{{ $t('TECH.407') }}</option>
						<option value="7">{{ $t('TECH.408') }}</option>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col c">
					<button type="submit">Отправить</button>
					<button @click.prevent="$emit('close')">Отмена</button>
				</div>
			</div>
		</div>
		<div class="separator"></div>
	</form>
</template>

<script>
	export default {
		name: "galaxy-missile-attack",
		props: {
			page: {
				type: Object
			},
			planet: {
				type: Number
			}
		},
		data () {
			return {
				target: 'all',
				count: this.page['user']['interplanetary_misil']
			}
		},
		methods: {
			send ()
			{
				this.$post('/rocket/', {
					galaxy: this.page['galaxy'],
					system: this.page['system'],
					planet: this.planet,
					count: this.count,
					target: this.target
				})
				.then((result) => {
					this.$store.commit('PAGE_LOAD', result)
				})
			}
		}
	}
</script>