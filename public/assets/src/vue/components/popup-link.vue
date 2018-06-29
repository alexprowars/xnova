<template>
	<a :href="to" @click.prevent="load" class="skip" :title="title">
		<slot></slot>
	</a>
</template>

<script>
	import { popup } from './../js/jquery.js'

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
				popup(this.title, this.to, this.width)
			}
		}
	}
</script>