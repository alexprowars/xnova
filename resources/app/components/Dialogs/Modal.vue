<template>
	<ModalRoot class="dialog-root">
		<ModalContent :class="[contentClass, { 'dialog-content--waiting': isTopmost && !isClosing }]">
			<ModalTitle class="sr-only">{{ title || $t('dialogs.title') }}</ModalTitle>
			<button type="button" class="dialog-close" :aria-label="$t('dialogs.close')" @click="closeModal">×</button>
			<ModalDescription as="div">
				<component :is="component" v-bind="componentAttrs" @close="closeModal" @close-modal="closeModal"/>
			</ModalDescription>
		</ModalContent>
	</ModalRoot>
</template>

<script setup>
	import { ModalContent, ModalDescription, ModalRoot, ModalTitle, useModalContext } from '@kolirt/vue-modal';

	const props = defineProps({
		component: {
			type: [Object, Function],
			required: true,
		},
		componentAttrs: {
			type: Object,
			default: () => ({}),
		},
		contentClass: {
			type: String,
			default: 'dialog-content',
		},
		title: {
			type: String,
			default: '',
		},
		persistent: {
			type: Boolean,
			default: false,
		},
	});

	const { close, isClosing, isTopmost, onBeforeClose } = useModalContext();

	if (props.persistent) {
		onBeforeClose(() => false);
	}

	function closeModal() {
		close({ ignoreGuard: true });
	}
</script>