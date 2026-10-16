<template>
	<PopoverRoot :open="open" @update:open="onOpenChange">
		<PopoverTrigger
			v-bind="$attrs"
			as-child
			tabindex="0"
			role="button"
			@click.capture="onTriggerClick"
			@keydown="onTriggerKeyDown"
			@pointerenter="onPointerEnter"
			@pointerleave="onPointerLeave"
		>
			<slot/>
		</PopoverTrigger>
		<PopoverPortal>
			<PopoverContent
				ref="popoverContent"
				class="ui-tooltip ui-popover"
				:class="popperClass"
				side="top"
				:side-offset="5"
				:collision-padding="12"
				:position-strategy="positionStrategy"
				@open-auto-focus.prevent
				@pointerenter="stopTimers"
				@pointerleave="onPointerLeave"
			>
				<slot name="content"/>
				<PopoverArrow class="ui-tooltip-arrow" :width="10" :height="5"/>
			</PopoverContent>
		</PopoverPortal>
	</PopoverRoot>
</template>

<script setup>
	import { PopoverArrow, PopoverContent, PopoverPortal, PopoverRoot, PopoverTrigger } from 'reka-ui';
	import { useTimeoutFn } from '@vueuse/core';
	import { ref, watch } from 'vue';

	defineOptions({ inheritAttrs: false });

	const props = defineProps({
		popperClass: String,
		openOnHover: Boolean,
		delayDuration: { type: Number, default: 200 },
		positionStrategy: { type: String, default: 'fixed' },
	});

	const open = ref(false);
	const popoverContent = ref(null);
	let openedByHover = false;
	let keepOpenOnClick = false;

	const { start: startOpenTimer, stop: stopOpenTimer } = useTimeoutFn(() => {
		openedByHover = true;
		open.value = true;
	}, () => props.delayDuration, { immediate: false });

	const { start: startCloseTimer, stop: stopCloseTimer } = useTimeoutFn(() => {
		open.value = false;
	}, 200, { immediate: false });

	watch(open, isOpen => {
		stopTimers();

		if (!isOpen) {
			openedByHover = false;
		}
	});

	function stopTimers() {
		stopOpenTimer();
		stopCloseTimer();
	}

	function onTriggerClick() {
		stopTimers();
		keepOpenOnClick = openedByHover;
		openedByHover = false;
	}

	function onOpenChange(isOpen) {
		open.value = isOpen || keepOpenOnClick;
		keepOpenOnClick = false;
	}

	function onPointerEnter(event) {
		if (props.openOnHover && event.pointerType === 'mouse') {
			stopCloseTimer();

			if (!open.value) {
				startOpenTimer();
			}
		}
	}

	function onPointerLeave(event) {
		if (!props.openOnHover || event.pointerType !== 'mouse') {
			return;
		}

		stopOpenTimer();

		if (!popoverContent.value?.$el?.contains(document.activeElement)) {
			startCloseTimer();
		}
	}

	function onTriggerKeyDown(event) {
		if (
			event.target !== event.currentTarget
			|| event.currentTarget.matches('button, a[href], input, select, textarea')
			|| !['Enter', ' '].includes(event.key)
		) {
			return;
		}

		event.preventDefault();
		event.currentTarget.click();
	}
</script>
