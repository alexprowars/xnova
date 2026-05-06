<template>
	<div class="confirm-box">
		<div v-if="title" class="dialog-title" v-html="title"></div>
		<div class="dialog-text" v-html="content"></div>
		<div class="dialog-buttons">
			<button v-for="button in buttons" type="button" class="btn" :class="button.class || ''" @click.stop="handle(button.handler)" v-html="button.title"></button>
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