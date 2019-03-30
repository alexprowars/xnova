<template>
	<section v-show="active" :aria-hidden="!active" class="tabs-component-panel" :id="hash" role="tabpanel">
		<slot/>
	</section>
</template>

<script>
	export default {
		props: {
			id: {
				default: null
			},
			name: {
				required: true
			},
		},
		data: () => ({
			active: false,
		}),
		computed:
		{
			header () {
				return this.name;
			},
			hash ()
			{
				return this.id ?
					'#' + this.id :
					'#' + this.name.toLowerCase().replace(/ /g, '-');
			},
		},
	};
</script>