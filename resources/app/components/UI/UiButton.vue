<template>
	<component :is="as" class="button ui-button" :class="['is-' + variant, { 'icon-button': icon }]" :type="as === 'button' ? type : undefined">
		<slot/>
	</component>
</template>

<script setup>
	defineProps({
		as: { type: [String, Object, Function], default: 'button' },
		type: { type: String, default: 'button' },
		variant: { type: String, default: 'primary', validator: value => ['primary', 'secondary', 'danger', 'success'].includes(value) },
		icon: Boolean,
	});
</script>
