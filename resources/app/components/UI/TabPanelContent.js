import { defineComponent } from 'vue';

export default defineComponent({
	name: 'TabPanelContent',
	props: {
		item: { type: Object, required: true },
		content: Function,
	},
	setup(props) {
		return () => props.content?.({ item: props.item });
	},
});
