<template>
	<Form action="/options" method="post" :on-success="() => useSuccessNotification(t('pages.options.saved'))" v-slot="{ errors, processing }">
		<div v-if="Object.keys(errors).length" class="options-errors" role="alert">
			<div v-for="(error, key) in errors" :key="key">{{ error }}</div>
		</div>
		<UiTabs :items="tabs" :label="$t('pages.options.title')">
			<template #information>
				<div class="options-panel">
					<div class="options-row">
						<label for="options-name" class="options-label">
							{{ $t('pages.options.nickname') }}
							<span class="options-hint">{{ $t('pages.options.nickname_change_notice') }}</span>
						</label>
						<div class="options-control">
							<input v-if="data.allow_name_change" id="options-name" name="name" :value="user.name" type="text" autocomplete="username">
							<span v-else class="options-value">{{ user.name }}</span>
						</div>
					</div>
					<div class="options-row">
						<label for="options-email" class="options-label">{{ $t('pages.options.email_address') }}</label>
						<div class="options-control">
							<input v-if="!user.email" id="options-email" type="email" name="email" value="" autocomplete="email">
							<div v-else class="options-inline">
								<span class="options-value">{{ user.email }}</span>
								<Link href="/options/email" class="button">{{ $t('pages.options.change') }}</Link>
							</div>
						</div>
					</div>
					<div class="options-row">
						<label for="options-sex" class="options-label">{{ $t('pages.options.sex') }}</label>
						<div class="options-control">
							<select id="options-sex" name="sex">
								<option value="M">{{ $t('pages.options.sex_male') }}</option>
								<option value="F" :selected="user.sex === 2">{{ $t('pages.options.sex_female') }}</option>
							</select>
						</div>
					</div>
					<div class="options-row">
						<label for="options-locale" class="options-label">{{ $t('pages.options.language') }}</label>
						<div class="options-control">
							<select id="options-locale" name="locale" v-model="user.locale">
								<option value="en">English</option>
								<option value="ru">Русский</option>
							</select>
						</div>
					</div>
					<div class="options-actions">
						<button type="submit" class="button" :disabled="processing">{{ $t('pages.options.save') }}</button>
					</div>
				</div>
			</template>
			<template #password>
				<div class="options-panel">
					<ChangePasswordForm/>
				</div>
			</template>
			<template #interface>
				<div class="options-panel">
					<h2 class="options-section-title">{{ $t('pages.options.planets_fleets') }}</h2>
					<div class="options-row">
						<label for="options-sort" class="options-label">{{ $t('pages.options.planet_sort_by') }}</label>
						<div class="options-control">
							<div class="options-control-stack">
								<select id="options-sort" name="settings_sort" v-model="user.options['planet_sort']">
									<option value="0">{{ $t('pages.options.sort_colonization_time') }}</option>
									<option value="1">{{ $t('pages.options.sort_coordinates') }}</option>
									<option value="2">{{ $t('pages.options.sort_alphabetical') }}</option>
									<option value="3">{{ $t('pages.options.sort_type') }}</option>
								</select>
								<select id="options-order" name="settings_order" :aria-label="$t('pages.options.sort_direction')" v-model="user.options['planet_sort_order']">
									<option value="0">{{ $t('pages.options.order_ascending') }}</option>
									<option value="1">{{ $t('pages.options.order_descending') }}</option>
								</select>
							</div>
						</div>
					</div>
					<div class="options-row">
						<label for="options-spy" class="options-label">{{ $t('pages.options.spy_probes_count') }}</label>
						<div class="options-control">
							<input id="options-spy" name="spy" :value="user.options['spy']" type="text" inputmode="numeric" class="options-number">
						</div>
					</div>
					<h2 class="options-section-title">{{ $t('pages.options.display') }}</h2>
					<div class="options-row options-switch-row">
						<label for="options-records" class="options-label">{{ $t('pages.options.participate_in_records') }}</label>
						<div class="options-control">
							<input name="records" value="" type="hidden">
							<input id="options-records" name="records" v-model="user.options['records']" type="checkbox" class="options-switch">
						</div>
					</div>
					<div class="options-row options-switch-row">
						<label for="options-bbcode" class="options-label">{{ $t('pages.options.use_bb_codes') }}</label>
						<div class="options-control">
							<input name="bbcode" value="" type="hidden">
							<input id="options-bbcode" name="bbcode" v-model="user.options['bb_parser']" type="checkbox" class="options-switch">
						</div>
					</div>
					<div class="options-row options-switch-row">
						<label for="options-available" class="options-label">{{ $t('pages.options.show_available_only') }}</label>
						<div class="options-control">
							<input name="available" value="" type="hidden">
							<input id="options-available" name="available" v-model="user.options['only_available']" type="checkbox" class="options-switch">
						</div>
					</div>
					<div class="options-row options-switch-row">
						<label for="options-chatbox" class="options-label">{{ $t('pages.options.show_chat_panel') }}</label>
						<div class="options-control">
							<input name="chatbox" value="" type="hidden">
							<input id="options-chatbox" name="chatbox" v-model="user.options['chatbox']" type="checkbox" class="options-switch">
						</div>
					</div>
					<h2 class="options-section-title">{{ $t('pages.options.messages_time') }}</h2>
					<div class="options-row">
						<label for="options-color" class="options-label">{{ $t('pages.options.message_color') }}</label>
						<div class="options-control">
							<select id="options-color" name="color" v-model="user.options['color']">
								<option v-for="id in Object.keys($tm('colors')).filter((c) => $t('colors.' + c + '.1') !== '')" :key="id" :value="id" :style="'color:' + $t('colors.' + id + '.0')">{{ $t('colors.' + id + '.1') }}</option>
							</select>
						</div>
					</div>
					<div class="options-row">
						<label for="options-timezone" class="options-label">{{ $t('pages.options.timezone') }}</label>
						<div class="options-control">
							<select id="options-timezone" name="timezone" v-model="user.options['timezone']">
								<option :value="null">{{ $t('pages.options.timezone_system') }}</option>
								<option v-for="i in timezones" :key="i" :value="i">{{ i > 0 ? '+' + i : i }}</option>
							</select>
						</div>
					</div>
					<h2 class="options-section-title">{{ $t('pages.options.avatar') }}</h2>
					<div class="options-avatar">
						<div v-if="user.photo" class="options-avatar-current">
							<img :src="user.photo" alt="" width="88" height="88">
							<label class="options-avatar-delete">
								<input type="checkbox" name="photo_delete" value="Y">
								{{ $t('pages.options.avatar_delete') }}
							</label>
						</div>
						<div class="options-avatar-upload">
							<label for="options-photo">{{ $t('pages.options.avatar_upload') }}</label>
							<input id="options-photo" type="file" name="photo" accept="image/jpeg,image/webp,image/png">
							<span class="options-hint">{{ $t('pages.options.avatar_resize_notice') }}</span>
						</div>
					</div>
					<div class="options-actions">
						<button type="submit" class="button" :disabled="processing">{{ $t('pages.options.save') }}</button>
					</div>
				</div>
			</template>
			<template #description>
				<div class="options-panel">
					<div class="options-editor">
						<TextEditor name="about" v-model="data.about"/>
					</div>
					<div class="options-actions">
						<button type="submit" class="button" :disabled="processing">{{ $t('pages.options.save') }}</button>
					</div>
				</div>
			</template>
			<template #vacation>
				<div class="options-panel">
					<section class="options-notice">
						<div class="options-notice-heading">
							<h2>{{ $t('pages.options.vacation_heading') }}</h2>
							<button type="button" @click.prevent="enableVacationMode" class="button" :title="$t('pages.options.vacation_mode_button_tooltip')">{{ $t('pages.options.vacation_mode_button') }}</button>
						</div>
						<div class="options-hint" v-html="$t('pages.options.vacation_mode_warning')"></div>
					</section>
					<section class="options-notice options-notice-danger">
						<div class="options-row options-switch-row">
							<label for="options-delete" class="options-label">{{ $t('pages.options.delete_on') }}</label>
							<div class="options-control">
								<input name="delete" value="0" type="hidden">
								<input id="options-delete" name="delete" value="1" :checked="user.deleted_at !== null" type="checkbox" class="options-switch">
							</div>
						</div>
						<div class="options-hint">{{ $t('pages.options.delete_profile_warning') }}</div>
					</section>
					<div class="options-actions">
						<button type="submit" class="button" :disabled="processing">{{ $t('pages.options.save') }}</button>
					</div>
				</div>
			</template>
			<template #auth>
				<div class="options-panel">
					<div v-if="data.auth.length" class="options-auth-wrap">
						<table class="options-auth-table">
							<thead>
								<tr>
									<th>{{ $t('pages.options.account') }}</th>
									<th>{{ $t('pages.options.registration_date') }}</th>
									<th>{{ $t('pages.options.last_login') }}</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="auth in data.auth" :key="auth.id">
									<td>
										<span class="options-auth-provider">{{ auth.provider }}</span>
										<span class="options-hint">{{ auth.provider_id }}</span>
									</td>
									<td>{{ $formatDate(auth.created_at, 'DD MMM YYYY HH:mm:ss') }}</td>
									<td>{{ auth.login_date ? $formatDate(auth.login_date, 'DD MMM YYYY HH:mm:ss') : '—' }}</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div v-else class="options-empty">{{ $t('pages.options.no_auth_accounts') }}</div>
				</div>
			</template>
		</UiTabs>
	</Form>
</template>

<script setup>
	import { UiTabs } from '~/components/UI';
	import useState from '~/composables/useState.js';
	import { computed, ref } from 'vue';
	import { useI18n } from 'vue-i18n';
	import ChangePasswordForm from './ChangePasswordForm.vue';
	import { Form, Link, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { openConfirmModal } from '~/composables/useModals.js';
	import TextEditor from '~/components/TextEditor.vue';

	defineProps({
		data: Object,
	})

	const { t } = useI18n();
	const state = useState();
	const user = computed(() => state.user);
	const tabs = computed(() => [
		{ id: 'information', label: t('pages.options.information') },
		...(user.value.email ? [{ id: 'password', label: t('pages.options.password') }] : []),
		{ id: 'interface', label: t('pages.options.interface') },
		{ id: 'description', label: t('pages.options.description') },
		{ id: 'vacation', label: t('pages.options.vacation_delete_tab') },
		{ id: 'auth', label: t('pages.options.auth_points') },
	]);

	const timezones = ref([]);

	for (let i = -12; i <= 12; i++) {
		timezones.value.push(i);
	}

	function enableVacationMode() {
		openConfirmModal(
			null,
			t('pages.options.vacation_confirm'),
			[{
				title: t('pages.options.cancel'),
			}, {
				title: t('pages.options.confirm'),
				handler() {
					useForm().post('/options/vacation', {
						preserveUrl: true,
					});
				}
			}]
		);
	}
</script>