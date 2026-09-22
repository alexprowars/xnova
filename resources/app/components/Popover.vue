<template>
	<PopoverRoot>
		<PopoverTrigger as-child>
			<slot/>
		</PopoverTrigger>
		<PopoverPortal>
			<PopoverContent class="ui-tooltip ui-popover" side="top" :side-offset="5" :collision-padding="12" @open-auto-focus.prevent>
				<slot name="content"/>
				<PopoverArrow class="ui-tooltip-arrow" :width="10" :height="5"/>
			</PopoverContent>
		</PopoverPortal>
	</PopoverRoot>
</template>

<script setup>
	import { PopoverArrow, PopoverContent, PopoverPortal, PopoverRoot, PopoverTrigger } from 'reka-ui';
</script>
