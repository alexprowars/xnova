<template>
	<Popover
		v-if="interactive"
		v-bind="$attrs"
		:popper-class="popperClass"
		:delay-duration="delayDuration"
		position-strategy="absolute"
		open-on-hover
	>
		<slot/>
		<template #content>
			<slot name="content">{{ content }}</slot>
		</template>
	</Popover>
	<TooltipProvider v-else :delay-duration="delayDuration">
		<TooltipRoot v-model:open="open" disable-closing-trigger>
			<TooltipTrigger v-bind="$attrs" as-child tabindex="0" @pointerup="onPointerUp">
				<slot/>
			</TooltipTrigger>
			<TooltipPortal>
				<TooltipContent class="ui-tooltip" :class="popperClass" side="top" :side-offset="5" :collision-padding="12" hide-when-detached @pointermove.capture="onContentPointerMove">
					<slot name="content">{{ content }}</slot>
					<TooltipArrow class="ui-tooltip-arrow" :width="10" :height="5"/>
				</TooltipContent>
			</TooltipPortal>
		</TooltipRoot>
	</TooltipProvider>
</template>

<script setup>
	import { TooltipArrow, TooltipContent, TooltipPortal, TooltipProvider, TooltipRoot, TooltipTrigger } from 'reka-ui';
	import { ref, useSlots } from 'vue';
	import Popover from './Popover.vue';

	defineOptions({ inheritAttrs: false });
	defineProps({
		content: String,
		interactive: Boolean,
		delayDuration: { type: Number, default: 200 },
		popperClass: String,
	});

	const open = ref(false);
	const slots = useSlots();

	function onContentPointerMove(event) {
		if (slots.content) {
			event.stopPropagation();
		}
	}

	function onPointerUp(event) {
		if (event.pointerType === 'touch') {
			open.value = !open.value;
		}
	}
</script>
