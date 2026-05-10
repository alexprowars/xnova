<template>
	<div class="block start">
		<div class="title">{{ $t('pages.start.main_info') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="grid">
					<div class="th middle">
						<div>
							{{ $t('pages.start.game_nickname') }}:
							<input :class="{error: v$.name.$error}" name="name" size="20" maxlength="30" type="text" v-model="form.name">
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
										<input type="radio" :value="'1_'+i" :id="'f1_'+i" v-model="form.avatar">
										<label :for="'f1_'+i" class="avatar">
											<img :src="'/assets/images/faces/1/'+i+'s.png'" alt="">
										</label>
									</div>
								</div>
							</Tab>
							<Tab :name="$t('pages.start.female')">
								<div class="grid grid-cols-4">
									<div v-for="i in 8">
										<input type="radio" :value="'2_'+i" :id="'f2_'+i" v-model="form.avatar">
										<label :for="'f2_'+i" class="avatar">
											<img :src="'/assets/images/faces/2/'+i+'s.png'" alt="">
										</label>
									</div>
								</div>
							</Tab>
						</Tabs>
					</div>
				</div>
				<div v-if="form.name && form.avatar" class="grid">
					<div class="th">
						<button class="button" @click.prevent="save">{{ $t('pages.start.continue') }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { useForm } from '@inertiajs/vue3';
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import Tabs from '~/components/Tabs.vue';
	import Tab from '~/components/Tab.vue';

	const state = useState();
	const user = computed(() => state.user);

	const form = useForm({
		name: user.value['name'],
		avatar: null,
	});

	const validations = {
		name: {
			required,
		}
	};

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function save() {
		if (!await v$.value.$validate()) {
			return;
		}

		form.post('/start', {
			preserveUrl: true,
		});
	}
</script>
