<template>
	<Head title="Технологии"/>
	<Tabs class="page-techtree">
		<Tab v-for="(group, i) in items" :key="i" :name="group['title']">
			<div v-for="list in group['items']" class="grid grid-cols-2">
				<div class="title flex items-center justify-between">
					<Link :href="'/info/' + list['id']">{{ list['name'] }}</Link>
					<div v-if="list['required'] !== null" class="float-end hidden sm:block">
						<Link :href="'/tech/' + list['id']">[i]</Link>
					</div>
				</div>
				<div v-html="list['required']"></div>
			</div>
		</Tab>
	</Tabs>
</template>

<script setup>
	import { Head, Link } from '@inertiajs/vue3';
	import Tabs from '~/components/Tabs.vue';
	import Tab from '~/components/Tab.vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		items: {
			type: Array,
			default: () => []
		}
	});
</script>