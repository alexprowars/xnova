<template>
	<div style="border: 1px solid #9999FF;">
		<div :style="{backgroundColor: color, width: value+'%'}">&nbsp;</div>
	</div>
</template>

<script>
	export default {
		name: "resources-bar",
		props: {
			value: {
				type: Number,
				default: 0
			},
			reverse: {
				type: Boolean,
				default: false
			}
		},
		computed: {
			width () {
				return Math.max(0, Math.min(100, this.value))
			},
			color ()
			{
				let val = this.width

				if (this.reverse)
					val = 100 - val

				if (val >= 100)
					return '#C00000';
				else if (val >= 80)
					return '#C0C000';
				else
					return '#00C000';
			}
		}
	}
</script>