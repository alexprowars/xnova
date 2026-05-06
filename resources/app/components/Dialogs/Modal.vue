<template>
	<vue-final-modal
		v-bind="$attrs"
		content-class="absolute inset-0"
		content-transition="vfm-modal"
		:reserve-scroll-bar-gap="false"
	>
		<div
			class="absolute inset-0 h-full overflow-auto"
			@click.self="() => emit('update:modelValue', false)"
		>
			<div class="dialog-content">
				<button class="dialog-close" @click="emit('update:modelValue', false)">×</button>
				<slot></slot>
			</div>
		</div>
	</vue-final-modal>
</template>

<script setup>
	import { VueFinalModal } from 'vue-final-modal';

	const emit = defineEmits(['update:modelValue'])
</script>

<script>
	export default {
		inheritAttrs: false,
	}
</script>