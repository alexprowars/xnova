<template>
	<Head :title="'Задание. ' + page['title']"/>
	<div class="page-quests quests-detail">
		<div class="block">
			<div class="title">
				{{ page['title'] }}
			</div>
			<div class="content">
				<div class="block-table quests">
					<div class="grid">
						<div class="k text-left">
							<div class="grid grid-cols-12 gap-3 m-2">
								<div class="col-span-3 text-center">
									<img :src="'/assets/images/quests/' + page['id'] + '.jpg'" class="inline" alt="">
								</div>
								<div class="col-span-9 text-left">
									<div class="description" v-html="page['description']"></div>
									<div class="text-xl mt-4">Задачи:</div>
									<ul>
										<li v-for="task in page['task']">
											<span v-html="task[0]"></span>
											<span>
												<img :src="'/assets/images/'+(task[1] ? 'check' : 'none')+'.gif'" height="11" width="12" alt="">
											</span>
										</li>
									</ul>
									<div style="color:orange;">
										Награда: <span v-html="page['rewd']"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="grid">
						<div class="k text-center">
							<input v-if="!page['errors']" type="button" class="end" @click.prevent="finish" value="Закончить">
							<div v-if="page['solution']" class="solution m-2" v-html="page['solution']"></div>
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
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	function finish() {
		useForm().post('/quests/' + props.page['id'], {
			preserveUrl: true,
			onSuccess() {
				useSuccessNotification('Квест завершен');
			}
		});
	}
</script>