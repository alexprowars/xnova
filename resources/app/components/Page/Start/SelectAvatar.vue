<template>
	<div class="block start">
		<div class="title">{{ $t('pages.start.main_info') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="grid">
					<div class="th middle">
						<div>
							{{ $t('pages.start.game_nickname') }}:
							<input :class="{error: v$.name.$error}" name="name" size="20" maxlength="30" type="text" v-model="name">
						</div>
					</div>
				</div>
				<div class="grid">
					<div class="c">{{ $t('pages.start.game_avatar') }}</div>
				</div>
				<div class="grid">
					<div class="th">
						<Tabs>
							<Tab :name="$t('pages.start.male')">
								<div class="grid grid-cols-4">
									<div v-for="i in 8">
										<input type="radio" :value="'1_'+i" :id="'f1_'+i" v-model="avatar">
										<label :for="'f1_'+i" class="avatar">
											<img :src="'/assets/images/faces/1/'+i+'s.png'" alt="">
										</label>
									</div>
								</div>
							</Tab>
							<Tab :name="$t('pages.start.female')">
								<div class="grid grid-cols-4">
									<div v-for="i in 8">
										<input type="radio" :value="'2_'+i" :id="'f2_'+i" v-model="avatar">
										<label :for="'f2_'+i" class="avatar">
											<img :src="'/assets/images/faces/2/'+i+'s.png'" alt="">
										</label>
									</div>
								</div>
							</Tab>
						</Tabs>
					</div>
				</div>
				<div v-if="name && avatar" class="grid">
					<div class="th">
						<button @click.prevent="save">{{ $t('pages.start.continue') }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { computed, ref } from 'vue';
	import { router, usePage } from '@inertiajs/vue3';
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import Tabs from '../../Tabs.vue';
	import Tab from '../../Tab.vue';
	import { useErrorNotification } from '../../../composables/useToast.js';
	import { useApiPost } from '../../../composables/useApi.js';

	const page = usePage();
	const user = computed(() => page.props.user);

	const name = ref(user.value['name']);
	const avatar = ref(null);

	const validations = {
		name: {
			required,
		}
	};

	const v$ = useVuelidate(
		validations,
		{ name },
		{ $autoDirty: true }
	);

	async function save() {
		if (!await v$.value.$validate()) {
			return
		}

		try {
			await useApiPost('/start', { name, avatar });

			router.reload();
		} catch (e) {
			useErrorNotification(e.message);
		}
	}
</script>