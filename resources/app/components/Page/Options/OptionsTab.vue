<template>
	<form ref="formRef" method="post" @submit.prevent="send">
		<Tabs>
			<Tab :name="$t('pages.options.information')">
				<div class="block-table text-center">
					<div class="grid grid-cols-2">
						<div class="th flex-col middle">
							{{ $t('pages.options.nickname') }}
							<br>
							<span class="negative">{{ $t('pages.options.nickname_change_notice') }}</span>
						</div>
						<div class="th middle">
							<input v-if="data['allow_name_change']" name="name" size="20" :value="user.name" type="text" autocomplete="username">
							<template v-else>{{ user.name }}</template>
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.email_address') }}</div>
						<div class="th middle gap-2">
							<input v-if="!user.email" type="text" name="email" value="">
							<template v-else>
								{{ user.email }} <Link href="/options/email" class="button">{{ $t('pages.options.change') }}</Link>
							</template>
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.sex') }}</div>
						<div class="th middle">
							<select name="sex">
								<option value="M">{{ $t('pages.options.sex_male') }}</option>
								<option value="F" :selected="user.sex === 2" >{{ $t('pages.options.sex_female') }}</option>
							</select>
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.language') }}</div>
						<div class="th middle">
							<select name="locale" v-model="user.locale">
								<option value="en">English</option>
								<option value="ru">Русский</option>
							</select>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<button type="submit" class="button">{{ $t('pages.options.save') }}</button>
						</div>
					</div>
				</div>
			</Tab>
			<Tab v-if="user.email" :name="$t('pages.options.password')">
				<ChangePasswordForm/>
			</Tab>
			<Tab :name="$t('pages.options.interface')">
				<div class="block-table text-center">
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.planet_sort_by') }}</div>
						<div class="th middle">
							<div class="flex flex-col gap-2">
								<select name="settings_sort" style='width:170px' v-model="user.options['planet_sort']">
									<option value="0">{{ $t('pages.options.sort_colonization_time') }}</option>
									<option value="1">{{ $t('pages.options.sort_coordinates') }}</option>
									<option value="2">{{ $t('pages.options.sort_alphabetical') }}</option>
									<option value="3">{{ $t('pages.options.sort_type') }}</option>
								</select>

								<select name="settings_order" style='width:170px' v-model="user.options['planet_sort_order']">
									<option value="0">{{ $t('pages.options.order_ascending') }}</option>
									<option value="1">{{ $t('pages.options.order_descending') }}</option>
								</select>
							</div>
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.spy_probes_count') }}</div>
						<div class="th middle"><input name="spy" :value="user.options['spy']" type="text"></div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.participate_in_records') }}</div>
						<div class="th middle">
							<input name="records" value="" type="hidden">
							<input name="records" v-model="user.options['records']" type="checkbox">
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.use_bb_codes') }}</div>
						<div class="th middle">
							<input name="bbcode" value="" type="hidden">
							<input name="bbcode" v-model="user.options['bb_parser']" type="checkbox">
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.show_available_only') }}</div>
						<div class="th middle">
							<input name="available" value="" type="hidden">
							<input name="available" v-model="user.options['only_available']" type="checkbox">
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.show_chat_panel') }}</div>
						<div class="th middle">
							<input name="chatbox" value="" type="hidden">
							<input name="chatbox" v-model="user.options['chatbox']" type="checkbox">
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.message_color') }}</div>
						<div class="th middle">
							<select name="color" style='width:170px' v-model="user.options['color']">
								<option v-for="id in Object.keys($tm('colors')).filter((c) => $t('colors.' + c + '.1') !== '')" :value="id" :style="'color:'+$t('colors.' + id + '.0')">{{ $t('colors.' + id + '.1') }}</option>
							</select>
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.timezone') }}</div>
						<div class="th middle">
							<select name="timezone" style="width:170px" v-model="user.options['timezone']">
								<option :value="null">{{ $t('pages.options.timezone_system') }}</option>
								<option v-for="i in timezones" :value="i">{{ i > 0 ? '+' + i : i }}</option>
							</select>
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th middle">{{ $t('pages.options.avatar') }}</div>
						<div class="th middle flex-col gap-6">
							<div v-if="user.photo">
								<div><img :src="user.photo" class="h-52" alt=""></div>
								<label>
									<input type="checkbox" name="photo_delete" value="Y">
									{{ $t('pages.options.avatar_delete') }}
								</label>
							</div>
							<div>
								<div><input type="file" name="photo" value=""></div>
								<small>{{ $t('pages.options.avatar_resize_notice') }}</small>
							</div>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<button type="submit" class="button">{{ $t('pages.options.save') }}</button>
						</div>
					</div>
				</div>
			</Tab>
			<Tab :name="$t('pages.options.description')">
				<div class="block-table text-center">
					<div class="grid">
						<div class="th">
							<TextEditor v-model="data['about']"/>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<button type="submit" class="button">{{ $t('pages.options.save') }}</button>
						</div>
					</div>
				</div>
			</Tab>
			<Tab :name="$t('pages.options.vacation_delete_tab')">
				<div class="block-table text-center">
					<div class="grid">
						<div class="th text-center">
							<a @click.prevent="enableVacationMode" class="button" v-tooltip="$t('pages.options.vacation_mode_button_tooltip')">
								{{ $t('pages.options.vacation_mode_button') }}
							</a>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<span class="negative" v-html="$t('pages.options.vacation_mode_warning')"></span>
						</div>
					</div>
					<div class="grid grid-cols-2">
						<div class="th"><a :title="$t('pages.options.delete_profile_link')">{{ $t('pages.options.delete_on') }}</a></div>
						<div class="th">
							<input name="delete" value="0" type="hidden">
							<input name="delete" value="1" :checked="user.deleted_at !== null" type="checkbox">
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<span class="negative">{{ $t('pages.options.delete_profile_warning') }}</span>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<button type="submit" class="button">{{ $t('pages.options.save') }}</button>
						</div>
					</div>
				</div>
			</Tab>
			<Tab :name="$t('pages.options.auth_points')">
				<div v-if="data['auth'].length" class="block-table text-center !border-b">
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.options.account') }}</div>
						<div class="c">{{ $t('pages.options.registration_date') }}</div>
						<div class="c">{{ $t('pages.options.last_login') }}</div>
					</div>
					<div v-for="auth in data['auth']" class="grid grid-cols-3">
						<div class="th">{{ auth['provider'] }} {{ auth['provider_id'] }}</div>
						<div class="th">{{ $formatDate(auth['created_at'], 'DD MMM YYYY HH:mm:ss') }}</div>
						<div class="th">
							<template v-if="auth['login_date']">
								{{ $formatDate(auth['login_date'], 'DD MMM YYYY HH:mm:ss') }}
							</template>
							<template>
								-
							</template>
						</div>
					</div>
				</div>
				<div class="block-table text-center mt-2!">
					<div class="grid">
						<div class="c">{{ $t('pages.options.link_social_accounts') }}</div>
					</div>
					<div class="grid">
						<div class="th"></div>
					</div>
				</div>
			</Tab>
		</Tabs>
	</form>
</template>

<script setup>
	import { computed, ref } from 'vue';
	import ChangePasswordForm from './ChangePasswordForm.vue';
	import Tabs from '~/components/Tabs.vue';
	import Tab from '~/components/Tab.vue';
	import { Link, router, usePage } from '@inertiajs/vue3';
	import { useApiPost, useApiSubmit } from '~/composables/useApi.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { openConfirmModal } from '~/composables/useModals.js';
	import TextEditor from '~/components/TextEditor.vue';

	defineProps({
		data: Object,
	})

	const page = usePage();
	const user = computed(() => page.props.user);

	const formRef = ref(null);
	const timezones = ref([]);

	for (let i = -12; i <= 12; i++) {
		timezones.value.push(i);
	}

	function send() {
		useApiSubmit('/options', new FormData(formRef.value), () => {
			useSuccessNotification('Настройки успешно изменены');

			router.reload();
		});
	}

	function enableVacationMode() {
		openConfirmModal(
			null,
			'Включить режим отпуска?',
			[{
				title: 'Нет',
			}, {
				title: 'Да',
				async handler() {
					await useApiPost('/options/vacation');
					router.reload();
				}
			}]
		);
	}
</script>