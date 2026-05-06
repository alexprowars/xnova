<template>
	<Head title="Обучение"/>
	<div class="page-quests">
		<div class="block">
			<div class="title text-center">
				Текущие задания
			</div>
			<div class="content">
				<div class="block-table">
					<div class="flex divide-x" v-for="quest in data.items">
						<div class="th w-10">{{ quest['id'] }}</div>
						<div class="th w-10">
							<img :src="'/assets/images/'+(quest['finish'] ? 'check' : 'none')+'.gif'" class="inline" height="11" width="12" alt="">
						</div>
						<div class="th grow text-left">
							<Link v-if="quest['available']" :href="'/quests/' + quest['id']"><span class="positive">{{ quest['title'] }}</span></Link>
							<span v-else class="positive">{{ quest['title'] }}</span>
							<template v-if="quest['available'] === false && Object.keys(quest['required']).length > 0">
								<br><br>Требования:
									<div v-for="(req, key) in quest['required']">
										<span v-if="key === 'quest'" :class="[(!data.quests[req] || data.quests[req]['finish'] === 0) ? 'negative' : 'positive']">Выполнение задания №{{ req }}</span>
										<span v-else-if="key === 'level_minier'" :class="[user.lvl.mine.l < req ? 'negative' : 'positive']">Промышленный уровень {{ req }}</span>
										<span v-else-if="key === 'level_raid'" :class="[user.lvl.raid.l < req ? 'negative' : 'positive']">Военный уровень {{ req }}</span>
									</div>
							</template>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link, usePage } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		data: {
			type: Object,
		}
	});

	const page = usePage();
	const user = computed(() => page.props.user);
</script>