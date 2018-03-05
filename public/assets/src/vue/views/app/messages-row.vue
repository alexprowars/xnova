<template>
	<div class="information-message" :class="['c', item.type.replace('-static', '')]" v-html="item.text"></div>
</template>

<script>
	export default {
		name: "application-messages-row",
		props: ['item']
	}
</script>