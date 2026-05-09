<template>
	<form method="post" @submit.prevent="invite">
		<div v-if="friends.length > 0 || alliance.length > 0">
			<select v-model="form.userId" size="10" style="width:75%;">
				<option value="">{{ $t('pages.fleets.verband.invite_select_none') }}</option>
				<optgroup v-if="friends.length > 0" :label="$t('pages.fleets.verband.invite_optgroup_friends_list')">
					<option v-for="user in friends" :value="user['id']">{{ user['username'] }}</option>
				</optgroup>
				<optgroup v-if="alliance.length > 0" :label="$t('pages.fleets.verband.invite_optgroup_alliance_members')">
					<option v-for="user in alliance" :value="user['id']">{{ user['username'] }}</option>
				</optgroup>
			</select>
			<div class="separator"></div>
		</div>
		<input type="text" v-model="form.userName" size="40" :placeholder="$t('pages.fleets.verband.invite_username_placeholder')">
		<br>
		<button type="submit" class="button">{{ $t('pages.fleets.verband.invite_submit') }}</button>
	</form>
</template>

<script setup>
	import { ref } from 'vue';
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
		id: Number,
		friends: Array,
		alliance: Array,
	});

	const form = useForm({
		userId: '',
		userName: '',
	});

	function invite() {
		form.post('/fleet/verband/' + props['id'] + '/user');
	}
</script>