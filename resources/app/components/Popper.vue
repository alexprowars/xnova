<template>
	<TooltipProvider :delay-duration="200">
		<TooltipRoot v-model:open="open" disable-closing-trigger>
			<Trigger/>
			<TooltipPortal>
				<TooltipContent class="ui-tooltip" :class="popperClass" side="top" :side-offset="5" :collision-padding="12" hide-when-detached>
					<slot name="content" :shown="open" :hide="hide">{{ content }}</slot>
					<TooltipArrow class="ui-tooltip-arrow" :width="10" :height="5"/>
				</TooltipContent>
			</TooltipPortal>
		</TooltipRoot>
	</TooltipProvider>
</template>

<script setup>
	import { TooltipArrow, TooltipContent, TooltipPortal, TooltipProvider, TooltipRoot, TooltipTrigger } from 'reka-ui';
	import { h, ref, Text, useAttrs, useSlots } from 'vue';

	defineOptions({ inheritAttrs: false });
	defineProps({
		content: String,
		popperClass: String,
	});

	const open = ref(false);
	const attrs = useAttrs();
	const slots = useSlots();

	const Trigger = () => h(TooltipTrigger, {
		...attrs,
		asChild: true,
		tabindex: 0,
		onPointerup: onPointerUp,
	}, {
		default: () => slots.default?.().filter(node => node.type !== Text || node.children.trim()),
	});

	function hide() {
		open.value = false;
	}

	function onPointerUp(event) {
		if (event.pointerType === 'touch') {
			open.value = !open.value;
		}
	}
</script>
