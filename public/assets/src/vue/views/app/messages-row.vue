<template>
	<table class="table"><tr><td :class="['c', item.type]" align="center" v-html="item.text"></td></tr></table><div class="separator"></div>
</template>

<script>
	export default {
		name: "application-messages-row",
		props: ['item']
	}
</script>