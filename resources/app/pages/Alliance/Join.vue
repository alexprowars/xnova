<template>
	<Head :title="$t('pages.alliance.join.meta_title')"/>
	<div class="block">
		<div class="title">
			{{ $t('pages.alliance.join.heading_title', [data['tag']]) }}
		</div>
		<div class="content">
			<form class="block-table text-center" @submit.prevent="send">
				<template v-if="data['text']">
					<div>
						<div class="c">{{ $t('pages.alliance.join.alliance_welcome_heading') }}</div>
					</div>
					<div>
						<div class="b min-h-20 p-2 text-left">
							<TextViewer :text="data['text']"/>
						</div>
					</div>
				</template>
				<div>
					<div class="th"><textarea cols="40" rows="10" v-model="form.message"></textarea></div>
				</div>
				<div>
					<div class="c"><button type="submit">{{ $t('pages.alliance.join.submit_request') }}</button></div>
				</div>
			</form>
		</div>
	</div>
	<div class="mt-2">
		<Link href="/alliance" class="button">{{ $t('pages.alliance.join.nav_back') }}</Link>
	</div>
</template>

<script setup>
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';
	import { useSuccessNotification } from '~/composables/useToast.js';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		data: Object,
	});

	const { t } = useI18n();

	const form = useForm({
		message: '',
	});

	function send() {
		form.post('/alliance/join/' + props.data['id'], {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.join.success_after_submit'));
			}
		});
	}
</script>