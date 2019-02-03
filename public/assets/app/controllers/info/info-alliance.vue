<template>
	<div class="block">
		<div class="title">Отправка топлива</div>
		<div class="content border-0">
			<router-form action="/info/34/">
				<div class="table">
					<div class="row">
						<div class="col th">Флоты на удержании возле планеты</div>
					</div>
					<div class="row">
						<div class="col th">
							<select name="fleet" v-model="fleet">
								<option :value="0">-</option>
								<option v-for="fleet in data['fleets']" :value="fleet['id']">[{{ fleet['galaxy'] }}:{{ fleet['system'] }}:{{ fleet['planet'] }}] {{ fleet['name']}}</option>
							</select>
						</div>
					</div>
					<div v-if="fleet > 0" class="row">
						<div class="col th">
							<button name="send" type="submit" value="Y">Отправить {{ data['cost'] }} дейтерия</button>
						</div>
					</div>
				</div>
			</router-form>
		</div>
	</div>
</template>

<script>
	export default {
		name: "info-alliance",
		props: {
			data: Object
		},
		data () {
			return {
				fleet: 0
			}
		}
	}
</script>