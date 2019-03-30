<template>
	<div class="information-message c" :class="[item.type.replace('-static', '')]" v-html="item.text"></div>
</template>

<script>
	export default {
		name: "messages-row",
		props: {
			item: {
				type: Object
			}
		}
	}
</script>