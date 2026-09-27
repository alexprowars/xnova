<template>
	<Head :title="page.title" />
	<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
		<h1>{{ page.title }}</h1>
		<div class="admin-toolbar">
			<Link v-if="page.canCreate" :href="`/admin/${page.resource}/create`" class="admin-button primary">
				+ {{ labels.create }}
			</Link>
			<Link v-if="page.resource === 'messages' && inertia.props.admin.permissions.includes('mailing')" href="/admin/messages/mailing" class="admin-button primary">
				{{ labels.mailing }}
			</Link>
		</div>
	</div>
	<form @submit.prevent="search" class="admin-card admin-filters p-4 mb-4 flex flex-wrap gap-3 items-end">
		<label v-for="field in page.filters" :key="field.key" class="admin-field w-36 grow max-w-xs">
			<span class="text-xs">{{ field.label }}</span>
			<select v-if="field.type === 'select'" v-model="filters[field.key]">
				<option value="">{{ labels.all }}</option>
				<option v-for="option in field.options" :key="option.value" :value="option.value">
					{{ option.label }}
				</option>
			</select>
			<input v-else v-model="filters[field.key]" type="text" />
		</label>
		<label class="admin-field w-24">
			<span class="text-xs">{{ labels.per_page }}</span>
			<select v-model="filters.per_page">
				<option v-for="size in [10, 20, 50, 100]" :key="size" :value="size">
					{{ size }}
				</option>
			</select>
		</label>
		<button class="admin-button primary">{{ labels.filter }}</button>
		<Link :href="`/admin/${page.resource}`" class="admin-button">{{ labels.reset }}</Link>
	</form>
	<DataTable :table="page.table">
		<template #actions="{ row }">
			<RecordActions
				:resource="page.resource"
				:id="row.id"
				:blocked="row.is_blocked"
				:can-return="row.can_return"
			/>
		</template>
	</DataTable>
</template>

<script setup>
	import { computed, reactive, watch } from 'vue';
	import { Head, Link, router, usePage } from '@inertiajs/vue3';
	import DataTable from '~/components/Admin/DataTable.vue';
	import RecordActions from '~/components/Admin/RecordActions.vue';

	const props = defineProps({ page: Object });
	const inertia = usePage();
	const labels = computed(() => inertia.props.admin.labels);
	const filters = reactive({ ...props.page.values, per_page: props.page.table.rows.per_page });

	watch(
		() => props.page.values,
		(values) => {
			Object.keys(filters).forEach((key) => delete filters[key]);
			Object.assign(filters, values, { per_page: props.page.table.rows.per_page });
		},
	);

	function search() {
		router.get('/admin/' + props.page.resource, filters, {
			preserveState: true,
			replace: true,
		});
	}
</script>
