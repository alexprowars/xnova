<template>
	<Head :title="$t('pages.alliance.info.head_title', [page['name']])"/>
	<div class="block">
		<div class="title">
			{{ $t('pages.alliance.info.page_heading') }}
		</div>
		<div class="content">
			<div class="block-table text-center">
				<div v-if="page['image']">
					<div class="th"><img :src="page['image']" style="max-width:100%" alt=""></div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_tag') }}</div>
					<div class="th">{{ page['tag'] }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_name') }}</div>
					<div class="th">{{ page['name'] }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_members') }}</div>
					<div class="th">{{ page['member_scount'] }}</div>
				</div>
				<div v-if="page['description']">
					<div class="b min-h-20 p-2 text-left">
						<TextViewer :text="page['description']"/>
					</div>
				</div>
				<div v-if="page['web']" class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_website') }}</div>
					<div class="th"><a :href="page['web']" target="_blank">{{ page['web'] }}</a></div>
				</div>
				<div v-if="page['request']">
					<div class="th">
						<Link :href="'/alliance/join/' + page['id']" class="button">{{ $t('pages.alliance.info.button_join') }}</Link>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import TextViewer from '~/components/TextViewer.vue';
	import { Head, Link } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	});
</script>