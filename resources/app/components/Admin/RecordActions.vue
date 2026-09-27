<template>
	<div class="admin-toolbar admin-row-actions">
		<ActionButton
			v-if="!editing && !['fleets', 'messages'].includes(resource)"
			:href="`/admin/${resource}/${id}/edit`"
			icon="edit"
			:label="labels.edit"
		/>
		<template v-if="resource === 'users'">
			<ActionButton
				v-if="!blocked && can('users-block')"
				icon="ban"
				:label="labels.ban"
				:compact="!editing"
				class="danger"
				@click="act('ban')"
			/>
			<ActionButton
				v-if="blocked && can('users-unblock')"
				icon="unban"
				:label="labels.unban"
				:compact="!editing"
				@click="act('unban')"
			/>
			<ActionButton
				v-if="editing"
				icon="vacation"
				:label="labels.vacation"
				:compact="false"
				@click="act('vacation')"
			/>
			<ActionButton
				v-if="editing"
				icon="delete"
				:label="labels.delete"
				:compact="false"
				class="danger"
				@click="act('delete')"
			/>
		</template>
		<template v-if="resource === 'fleets'">
			<ActionButton icon="accelerate" :label="labels.accelerate" @click="act('accelerate')" />
			<ActionButton
				v-if="canReturn"
				icon="return"
				:label="labels.return"
				@click="act('return')"
			/>
		</template>
		<ActionButton
			v-if="['planets', 'moons', 'fleets', 'messages'].includes(resource)"
			icon="delete"
			:label="labels.delete"
			:compact="!editing"
			class="danger"
			@click="act('delete')"
		/>
		<ActionDialog ref="dialog" />
	</div>
</template>

<script setup>
	import { computed, ref } from 'vue';
	import { usePage } from '@inertiajs/vue3';
	import ActionDialog from './ActionDialog.vue';
	import ActionButton from './ActionButton.vue';
	const props = defineProps({
		resource: String,
		id: Number,
		blocked: Boolean,
		editing: Boolean,
		canReturn: Boolean,
	});
	const page = usePage();
	const labels = computed(() => page.props.admin.labels);
	const can = (permission) => page.props.admin.permissions.includes(permission);
	const dialog = ref();
	const field = (key, type) => ({ key, type, label: labels.value[key] });
	function act(action) {
		let url = `/admin/${props.resource}/${props.id}/actions`;
		let method = 'post';
		const data = { action };
		const fields = [];
		if (['ban', 'vacation'].includes(action)) {
			data.until = '';
			fields.push(field('until', 'datetime-local'));
		}
		if (action === 'ban') {
			data.reason = '';
			fields.push(field('reason', 'text'));
		}
		if (action === 'delete' && ['users', 'planets', 'moons'].includes(props.resource)) {
			data.immediate = false;
			fields.push(field('immediate', 'checkbox'));
		}
		if (['planets', 'moons'].includes(props.resource)) {
			url = `/admin/${props.resource}/${props.id}/delete`;
		}
		if (props.resource === 'messages') {
			url = `/admin/messages/${props.id}`;
			method = 'delete';
		}
		dialog.value.open({
			title: `${labels.value[action]} · #${props.id}`,
			description:
				action === 'delete' && fields.length ? labels.value.delete_help : undefined,
			url,
			method,
			data,
			fields,
			danger: ['ban', 'delete'].includes(action),
		});
	}
</script>
