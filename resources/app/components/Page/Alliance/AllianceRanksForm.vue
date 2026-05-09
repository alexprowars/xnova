<template>
	<div class="block">
		<div class="title">Установить ранги</div>
		<div class="content">
			<form ref="ranksRef" @submit.prevent="save" class="block-table text-center">
				<div class="flex">
					<div class="basis-1/6 th">Имя ранга</div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r1.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r2.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r3.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r4.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r5.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r6.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r7.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r8.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r9.png" width="16" alt=""></div>
					<div class="basis-1/12 th"><img src="/assets/images/alliance/r10.gif" width="16" alt=""></div>
				</div>
				<div v-for="rank in items" class="flex">
					<div class="basis-1/12 th">
						<a href="" @click.prevent="remove(rank['id'])"><img src="/assets/images/abort.gif" alt="Удалить ранг"></a>
					</div>
					<div class="basis-1/12 th">{{ rank['name'] }}</div>
					<div class="basis-1/12 th">
						<input v-if="owner" type="checkbox" :name="'rights[' + rank['id'] + '][delete]'" v-model="rank['rights']['delete']">
						<b v-else>{{ (rank['rights']['delete'] || false) ? '+' : '-' }}</b>
					</div>
					<div class="basis-1/12 th">
						<input v-if="owner" type="checkbox" :name="'rights[' + rank['id'] + '][kick]'" v-model="rank['rights']['kick']">
						<b v-else>{{ (rank['rights']['kick'] || false) ? '+' : '-' }}</b>
					</div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][request]'" v-model="rank['rights']['request']"></div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][memberlist]'" v-model="rank['rights']['memberlist']"></div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][accept]'" v-model="rank['rights']['accept']"></div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][admin]'" v-model="rank['rights']['admin']"></div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][onlinestatus]'" v-model="rank['rights']['onlinestatus']"></div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][chat]'" v-model="rank['rights']['chat']"></div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][rights]'" v-model="rank['rights']['rights']"></div>
					<div class="basis-1/12 th"><input type="checkbox" :name="'rights[' + rank['id'] + '][diplomacy]'" v-model="rank['rights']['diplomacy']"></div>
				</div>
				<div v-if="items.length > 0" class="grid">
					<div class="c"><button type="submit" class="button">Сохранить</button></div>
				</div>
				<div v-if="items.length === 0" class="grid">
					<div class="th">нет рангов</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
		owner: Boolean,
		items: Array,
	});

	const ranksRef = ref();

	function save() {
		let data = {
			rigths: {}
		};

		for (let i in props.items) {
			let rank = props.items[i];

			data.rigths[rank['id']] = Object.fromEntries(Object.entries(rank['rights']).filter(([key, value]) => value === true));
		}

		useForm(data).post('/alliance/admin/ranks');
	}

	function remove(id) {
		useForm().delete('/alliance/admin/ranks/' + id);
	}
</script>