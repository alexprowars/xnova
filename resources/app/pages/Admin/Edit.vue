<template>
	<Head :title="page.title" />
	<div class="space-y-5">
		<Link v-if="page.resource !== 'settings'" :href="`/admin/${page.resource}`" class="admin-muted text-xs">
			← {{ labels.back_list }}
		</Link>
		<div class="flex flex-wrap gap-3 justify-between items-center">
			<h1>{{ page.title }}</h1>
			<RecordActions
				v-if="page.id && ['users', 'planets', 'moons'].includes(page.resource)"
				:resource="page.resource"
				:id="page.id"
				:blocked="page.status?.is_blocked"
				editing
			/>
		</div>
		<div v-if="page.links?.some((link) => link.url) || page.createMoon" class="admin-toolbar">
			<Link v-for="link in page.links.filter((link) => link.url)" :key="link.url" :href="link.url" class="admin-button">
				{{ link.text }} ↗
			</Link>
			<Link v-if="page.createMoon" :href="page.createMoon" class="admin-button">
				+ {{ labels.create_moon }}
			</Link>
		</div>
		<div v-if="page.status" class="admin-toolbar admin-muted text-xs">
			<span v-for="key in ['blocked_at', 'vacation', 'delete_time']" :key="key">
				{{ labels[key] }}: {{ page.status[key] || '—' }}
			</span>
		</div>
		<form @submit.prevent="save" class="admin-card">
			<div class="p-5 space-y-5">
				<p v-if="page.help" class="admin-muted">{{ page.help }}</p>
				<img v-if="page.image" :src="page.image" class="max-h-28 rounded" :alt="labels.photo"/>
				<Fields :fields="page.fields" :form="form" />
				<ul v-if="Object.keys(form.errors).length" class="admin-error space-y-1" role="alert">
					<li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
				</ul>
			</div>
			<div class="p-4 border-t border-[var(--admin-border)] flex items-center gap-3">
				<button class="admin-button primary" :disabled="form.processing">
					{{
						form.processing
							? labels.processing
							: page.confirm
								? labels.send
								: labels.save
					}}
				</button>
				<span v-if="form.recentlySuccessful" class="admin-muted text-xs" role="status">
					{{ labels.saved }}
				</span>
				<span v-if="form.progress" class="admin-muted">
					{{ form.progress.percentage }}%
				</span>
			</div>
		</form>
		<DataTable v-for="table in page.tables" :key="table.title" :table="table" />
	</div>
</template>

<script setup>
	import { computed, watch } from 'vue';
	import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
	import Fields from '~/components/Admin/Fields.vue';
	import DataTable from '~/components/Admin/DataTable.vue';
	import RecordActions from '~/components/Admin/RecordActions.vue';

	const props = defineProps({ page: Object });
	const inertia = usePage();
	const labels = computed(() => inertia.props.admin.labels);
	const values = () =>
		Object.fromEntries(
			props.page.fields.map((field) => [
				field.key,
				field.value ??
					(field.type === 'checkboxes' ? [] : field.type === 'checkbox' ? false : ''),
			]),
		);

	const form = useForm(values());

	watch(
		() => `${props.page.resource}:${props.page.id || 'create'}`,
		() => {
			form.defaults(values());
			form.reset();
			form.clearErrors();
		},
	);

	function save() {
		if (props.page.confirm && !window.confirm(labels.value.confirm_mailing)) {
			return;
		}

		form.transform(() =>
			Object.fromEntries(props.page.fields.map((field) => [field.key, form[field.key]])),
		).post(props.page.url, {
			preserveScroll: true,
			onSuccess: () => {
				form.defaults(values());
				form.reset();
			},
		});
	}
</script>
