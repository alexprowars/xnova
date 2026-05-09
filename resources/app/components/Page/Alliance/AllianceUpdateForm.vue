<template>
	<div class="block">
		<div class="title">Дополнительные настройки</div>
		<div class="content">
			<form class="block-table text-center" @submit.prevent="save">
				<div class="grid grid-cols-4">
					<div class="th">Домашняя страница</div>
					<div class="th col-span-3">
						<input type="text" name="web" class="w-full!" v-model="form.web">
					</div>
				</div>
				<div class="grid grid-cols-4">
					<div class="th">Логотип</div>
					<div class="th col-span-3">
						<input class="w-full" type="file" name="image" @input="form.image = $event.target.files[0]">
						<template v-if="data['image']">
							<img :src="data['image']" style="max-width: 98%;max-height: 400px;" alt="">
							<label>
								<input type="checkbox" name="delete_image" value="Y" v-model="form.delete_image"> Удалить
							</label>
						</template>
					</div>
				</div>
				<div class="grid grid-cols-4">
					<div class="th">Ранг основателя</div>
					<div class="th col-span-3">
						<input class="w-full!" type="text" name="owner_rank" v-model="form.owner_rank">
					</div>
				</div>
				<div class="grid grid-cols-4">
					<div class="th">Заявки</div>
					<div class="th col-span-3">
						<select class="w-full" name="request_notallow" v-model="form.public">
							<option value="0">Закрытый альянс</option>
							<option value="1">Открытый альянс</option>
						</select>
					</div>
				</div>
				<div class="grid">
					<div class="th">
						<button type="submit" class="button">Сохранить</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
		data: Object,
	});

	const form = useForm({
		web: props.data['web'],
		image: null,
		delete_image: null,
		owner_rank: props.data['owner_rank'],
		public: props.data['public'],
	});

	function save() {
		form.post('/alliance/admin', {
			preserveScroll: true,
			forceFormData: true,
		});
	}
</script>