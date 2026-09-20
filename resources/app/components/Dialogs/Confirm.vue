<template>
	<div>
		<div class="dialog-message">
			<div v-if="title" class="dialog-title" v-html="title"></div>
			<div v-if="content" class="dialog-text" :class="{ 'dialog-text--heading': !title }" v-html="content"></div>
		</div>
		<div class="dialog-buttons">
			<button
				v-for="(button, index) in buttons"
				:key="index"
				type="button"
				class="button"
				:class="[button.class, { 'is-secondary': typeof button.handler !== 'function' }]"
				@click.stop="handle(button.handler)"
				v-html="button.title"
			></button>
		</div>
	</div>
</template>

<script setup>
	const props = defineProps({
		title: {
			type: String,
			default: '',
		},
		content: {
			type: String,
			default: '',
		},
		buttons: {
			title: Object,
			default: () => {
				return {
					ok: {
						title: 'ok'
					}
				}
			}
		}
	})

	const emit = defineEmits(['close'])

	function handle (action) {
		if (typeof action === 'function') {
			action()
		}

		emit('close')
	}
</script>