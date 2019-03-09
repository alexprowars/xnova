<template>
	<div class="row flight">
		<div class="th col-4 col-sm-2">
			<div class="z">
				<timer :value="item[0]"></timer>
			</div>
			<span class="positive d-sm-none">{{ item[0]|date('d.m H:i:s') }}</span>
		</div>
		<div class="th col-sm-10 col-8 text-left">
			<span class="flight owndeploy">
				<router-link v-if="item[1] === $store.state.user.planet" :to="'/buildings/?chpl='+item[1]" style="color:#33ff33;">{{ item[2] }}</router-link><span v-else>{{ item[2] }}</span>:
			</span>
			<span class="holding colony">{{ item[3] }}</span>
			<span class="positive float-sm-right d-none d-sm-inline">{{ item[0]|date('d.m H:i:s') }}</span>
		</div>
	</div>
</template>

<script>
	export default {
		name: "overview-queue-row",
		props: {
			item: Array
		}
	}
</script>