<template>
	<div class="grid grid-cols-12">
		<div class="col-span-1 c">
			<Link :href="'/support/' + item['id']">
				{{ item['id'] }}
			</Link>
		</div>
		<div class="col-span-6 c">
			<Link :href="'/support/' + item['id']">
				{{ item['subject'] }}
			</Link>
		</div>
		<div class="col-span-2 c">
			<span v-if="item['status'] === 0" style="color:red">{{ $t('pages.support.status.0') }}</span>
			<span v-if="item['status'] === 1" style="color:green">{{ $t('pages.support.status.1') }}</span>
			<span v-if="item['status'] === 2" style="color:orange">{{ $t('pages.support.status.2') }}</span>
			<span v-if="item['status'] === 3" style="color:green">{{ $t('pages.support.status.3') }}</span>
		</div>
		<div class="col-span-3 c">
			{{ $formatDate(item['updated_at'], 'DD MMM YYYY HH:mm:ss') }}
		</div>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';

	defineProps({
		item: {
			type: Object,
		}
	})
</script>