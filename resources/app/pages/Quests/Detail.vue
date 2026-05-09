<template>
	<Head :title="'Задание. ' + data['title']"/>
	<div class="page-quests quests-detail">
		<div class="block">
			<div class="title">
				{{ data['title'] }}
			</div>
			<div class="content">
				<div class="block-table quests">
					<div class="grid">
						<div class="k text-left">
							<div class="grid grid-cols-12 gap-3 m-2">
								<div class="col-span-3 text-center">
									<img :src="'/assets/images/quests/' + data['id'] + '.jpg'" class="inline" alt="">
								</div>
								<div class="col-span-9 text-left">
									<div class="description" v-html="data['description']"></div>
									<div class="text-xl mt-4">Задачи:</div>
									<ul>
										<li v-for="task in data['task']">
											<span v-html="task[0]"></span>
											<span>
												<img :src="'/assets/images/'+(task[1] ? 'check' : 'none')+'.gif'" height="11" width="12" alt="">
											</span>
										</li>
									</ul>
									<div style="color:orange;">
										Награда: <span v-html="data['rewd']"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="grid">
						<div class="k text-center">
							<input v-if="!data['errors']" type="button" class="end" @click.prevent="finish" value="Закончить">
							<div v-if="data['solution']" class="solution m-2" v-html="data['solution']"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-2">
			<Link href="/quests" class="button">Назад</Link>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link, router } from '@inertiajs/vue3';
	import { useApiSubmit } from '~/composables/useApi.js';
	import { useSuccessNotification } from '~/composables/useToast.js';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		data: {
			type: Object,
		}
	});

	function finish() {
		useApiSubmit('quests/' + props.data['id'], {}, () => {
			useSuccessNotification('Квест завершен');

			router.visit('/quests');
		});
	}
</script>