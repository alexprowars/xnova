<template>
	<input ref="input" v-model="formatValue" type="number" @input="update($event.target.value)">
</template>

<script>
	export default {
		name: "input-zero",
		props: {
			value: {
				type: Number,
				default: 0,
			}
		},
		data() {
			return {
				formatValue: parseInt(this.value) === 0 ? '' : this.value
			}
		},
		watch: {
			formatValue (value) {
				this.formatValue = parseInt(value) === 0 ? '' : value;
			}
		},
		methods: {
			update (value)
			{
				value = parseInt(value);

				if (isNaN(value))
					value = 0;

				this.$emit('input', value);
			}
		}
	}
</script>