<template>
	<a :href="to" @click.prevent="load" class="skip" :title="title">
		<slot></slot>
	</a>
</template>

<script>
	export default {
		name: "popup-link",
		props: {
			to: String,
			width: {
				type: Number,
				default: 600
			},
			title: {
				type: String,
				default: ''
			},
		},
		methods: {
			load () {
				this.$root.openPopup(this.title, this.to, this.width)
			}
		}
	}
</script>