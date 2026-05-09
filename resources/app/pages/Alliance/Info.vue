<template>
	<Head :title="$t('pages.alliance.info.head_title', [data['name']])"/>
	<div class="block">
		<div class="title">
			{{ $t('pages.alliance.info.page_heading') }}
		</div>
		<div class="content">
			<div class="block-table text-center">
				<div v-if="data['image']">
					<div class="th"><img :src="data['image']" style="max-width:100%" alt=""></div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_tag') }}</div>
					<div class="th">{{ data['tag'] }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_name') }}</div>
					<div class="th">{{ data['name'] }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_members') }}</div>
					<div class="th">{{ data['member_scount'] }}</div>
				</div>
				<div v-if="data['description']">
					<div class="b min-h-20 p-2 text-left">
						<TextViewer :text="data['description']"/>
					</div>
				</div>
				<div v-if="data['web']" class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.info.label_website') }}</div>
					<div class="th"><a :href="data['web']" target="_blank">{{ data['web'] }}</a></div>
				</div>
				<div v-if="data['request']">
					<div class="th">
						<Link :href="'/alliance/join/' + data['id']" class="button">{{ $t('pages.alliance.info.button_join') }}</Link>
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
		data: Object,
	});
</script>