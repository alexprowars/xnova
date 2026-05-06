<template>
	<Tooltip v-bind="attrs" placement="top" :triggers="['hover', 'focus', 'touch']" :popper-triggers="['hover']" :aria-id="ariaId">
		<slot/>
		<template #popper="props">
			<slot name="content" v-bind="props"/>
		</template>
	</Tooltip>
</template>

<script setup>
	import { Tooltip } from 'floating-vue';
	import { useAttrs, useId } from 'vue';

	const attrs = useAttrs();
	const ariaId = useId()
</script>