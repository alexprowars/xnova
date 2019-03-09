<template>
	<div v-if="missile['interceptor'] > 0 || missile['interplanetary'] > 0" class="block">
		<div class="title">Уничтожитель ракет</div>
		<div class="content border-0">
			<router-form :action="'/info/'+item+'/'">
				<input type="hidden" name="missiles" value="Y">
				<div class="table">
					<div v-if="missile['interceptor'] > 0" class="row">
						<div class="col th">{{ $t('TECH.502') }}: {{ missile['interceptor'] }}</div>
						<div class="col th"><number name="interceptor" v-model="interceptor"></number></div>
					</div>
					<div v-if="missile['interplanetary'] > 0" class="row">
						<div class="col th">{{ $t('TECH.502') }}: {{ missile['interplanetary'] }}</div>
						<div class="col th"><number name="interplanetary" v-model="interplanetary"></number></div>
					</div>
					<div v-if="interceptor > 0 || interplanetary > 0" class="row">
						<div class="col th">
							<input type="submit" value="Уничтожить">
						</div>
					</div>
				</div>
			</router-form>
		</div>
	</div>
</template>

<script>
	export default {
		name: "info-missile",
		props: {
			item: Number,
			missile: Object
		},
		data () {
			return {
				interceptor: 0,
				interplanetary: 0
			}
		}
	}
</script>