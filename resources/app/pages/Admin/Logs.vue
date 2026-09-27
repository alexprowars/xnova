<template>
	<Head :title="page.title" />
	<div class="space-y-5">
		<Link :href="`/admin/users/${page.id}/edit`" class="admin-muted text-xs">
			← {{ labels.back_edit }}
		</Link>
		<h1>{{ page.title }}</h1>
		<nav class="admin-toolbar">
			<Link
				v-for="type in ['transfers', 'ips', 'credits', 'attacks', 'referrals', 'friends']"
				:key="type"
				:href="`/admin/users/${page.id}/logs?type=${type}`"
				class="admin-button"
				:class="{ primary: page.type === type }"
				:aria-current="page.type === type ? 'page' : undefined"
			>
				{{ labels[type] }}
			</Link>
		</nav>
		<DataTable :table="page.table" />
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { Head, Link, usePage } from '@inertiajs/vue3';
	import DataTable from '~/components/Admin/DataTable.vue';

	defineProps({ page: Object });

	const inertia = usePage();
	const labels = computed(() => inertia.props.admin.labels);
</script>
