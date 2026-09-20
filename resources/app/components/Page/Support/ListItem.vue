<template>
	<tr>
		<td class="support-ticket-id">
			<Link :href="'/support/' + item.id">#{{ item.id }}</Link>
		</td>
		<td>
			<Link :href="'/support/' + item.id" class="support-subject">{{ item.subject }} <span aria-hidden="true">↗</span></Link>
		</td>
		<td>
			<span class="support-status" :class="'status-' + item.status">{{ $t('pages.support.status.' + item.status) }}</span>
		</td>
		<td class="support-date">
			<time v-if="item.updated_at" :datetime="item.updated_at">
				{{ $formatDate(item.updated_at, 'DD MMM YYYY') }}
				<span>{{ $formatDate(item.updated_at, 'HH:mm:ss') }}</span>
			</time>
			<span v-else>—</span>
		</td>
	</tr>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';

	defineProps({
		item: {
			type: Object,
		}
	})
</script>