<template>
	<Head title="Постройки"/>
	<div class="page-building page-building-build">
		<BuildQueue :queue="queueByType('build')"/>

		<div class="buldings">
			<div class="buldings-header" :style="{ backgroundImage: 'url(\'/assets/images/buildings-bg.webp\')' }">
				<div class="buldings-header-main">
					<span class="title">
						Постройки / {{ planet['name'] }}
					</span>

					<div class="flex flex-col items-end gap-2 bg-black/80 p-3">
						<i18n-t keypath="pages.building.fields_used" tag="div" scope="global">
							<template v-slot:used>
								<span class="positive">{{ planet['field_used'] }}</span>
							</template><template v-slot:max>
								<span class="positive">{{ planet['field_max'] }}</span>
							</template>
						</i18n-t>
						<div>
							{{ $t('pages.building.fields_left') }}
							<span class="positive">{{ emptyFieldsCount }}</span> {{ $t('pages.building.fields_left_2', emptyFieldsCount) }}
						</div>
					</div>
					<Link href="/resources" class="button">
						Настройки ресурсов
					</Link>
				</div>
				<BuildActive v-if="activeItem" :item="activeItem" @close="selectAction(null)" @build="addAction"/>
			</div>
			<div class="buldings-list">
				<BuildItem v-for="(item, i) in page.items" :key="i"
					:class="{ active: activeElement === item['id'] }"
					:item="item"
					@select="selectAction(item['id'])"
					@build="addAction"
				/>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import BuildQueue from '~/components/Page/Buildings/BuildQueue.vue';
	import BuildItem from '~/components/Page/Buildings/BuildItem.vue';
	import BuildActive from '~/components/Page/Buildings/BuildActive.vue';
	import { computed, ref } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { queueByType, emptyFieldsCount } from '~/utils/buildings.js';

	const props = defineProps({
		page: Object,
	});

	const state = useState();
	const planet = computed(() => state.planet);

	const activeElement = ref(null);
	const activeItem = computed(() => {
		return props.page.items.filter((item) => item.id === activeElement.value)[0] || null;
	});

	function selectAction(id) {
		if (activeElement.value !== id) {
			activeElement.value = id;
		} else {
			activeElement.value = null;
		}
	}

	async function addAction (id) {
		useForm({ element: id })
			.post('/buildings/build/insert', {
				preserveUrl: true,
				preserveScroll: true,
			});
	}
</script>
