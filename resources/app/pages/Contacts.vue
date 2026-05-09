<template>
	<Head :title="$t('pages.contacts.page_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.contacts.section_heading') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="th">
					<span style="color: orange">{{ $t('pages.contacts.intro_hint') }}</span>
				</div>
				<div class="grid grid-cols-3">
					<div class="c">{{ $t('pages.contacts.table_header_name') }}</div>
					<div class="c">{{ $t('pages.contacts.table_header_role') }}</div>
					<div class="c">{{ $t('pages.contacts.table_header_email') }}</div>
				</div>
				<template v-for="item in items">
					<div class="grid grid-cols-3">
						<div class="th">{{ item['name'] }}</div>
						<div class="th">{{ item['role'] }}</div>
						<div class="th">
							<a :href="'mailto:'+item['email']">{{ item['email'] }}</a>
						</div>
					</div>
					<div class="c" v-if="item['about'].length > 0">
						<TextViewer :text="item['about']"/>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';

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
			default: () => [],
		}
	});
</script>