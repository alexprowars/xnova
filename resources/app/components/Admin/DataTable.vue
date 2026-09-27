<template>
	<section class="admin-card">
		<div
			class="px-4 py-3 flex justify-between items-center border-b border-[var(--admin-border)]"
		>
			<h2>{{ table.title }}</h2>
			<span class="admin-muted text-xs">{{ table.rows.total }}</span>
		</div>
		<div class="overflow-x-auto">
			<table class="admin-table">
				<thead>
					<tr>
						<th v-for="column in table.columns" :key="column.key" scope="col">
							{{ column.label }}
						</th>
						<th
							v-if="$slots.actions || actionRows"
							scope="col"
							class="admin-actions-cell"
						>
							{{ labels.actions }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="row in table.rows.data" :key="row.id">
						<td v-for="column in table.columns" :key="column.key">
							<template
								v-if="
									typeof row[column.key] === 'object' && row[column.key] !== null
								"
							>
								<Link v-if="row[column.key].url" :href="row[column.key].url">
									{{ text(row[column.key].text) }}
								</Link>
								<MessagePreview
									v-else-if="row[column.key].preview_url"
									:url="row[column.key].preview_url"
								/>
								<Popper
									v-else-if="row[column.key].tooltip"
									:content="row[column.key].tooltip"
									popper-class="admin-popper"
									:delay-duration="0"
								>
									<button type="button" class="admin-preview-button">
										{{ text(row[column.key].text) }}
									</button>
								</Popper>
								<span v-else class="whitespace-pre-line">
									{{ text(row[column.key].text) }}
								</span>
							</template>
							<details v-else-if="String(row[column.key] || '').length > 180">
								<summary class="cursor-pointer">
									{{ String(row[column.key]).slice(0, 100) }}…
								</summary>
								<pre class="whitespace-pre-wrap mt-2 font-inherit">{{
									row[column.key]
								}}</pre>
							</details>
							<span v-else class="whitespace-pre-line">
								{{ text(row[column.key]) }}
							</span>
						</td>
						<td v-if="$slots.actions || actionRows" class="admin-actions-cell">
							<div class="admin-toolbar admin-row-actions">
								<slot name="actions" :row="row" />
								<ActionButton
									v-if="row.action_url"
									icon="edit"
									:label="labels.edit"
									@click="changeLevel(row)"
								/>
								<template v-if="row.queue_url">
									<ActionButton
										icon="complete"
										:label="labels.complete"
										@click="queueAction(row, 'complete')"
									/>
									<ActionButton
										icon="delete"
										:label="labels.delete"
										class="danger"
										@click="queueAction(row, 'delete')"
									/>
								</template>
								<template v-if="row.member_url">
									<ActionButton
										icon="rank"
										:label="labels.rank"
										@click="memberAction(row, 'rank')"
									/>
									<ActionButton
										v-if="!row.leader"
										icon="leader"
										:label="labels.make_leader"
										@click="memberAction(row, 'leader')"
									/>
									<ActionButton
										v-if="!row.leader"
										icon="delete"
										:label="labels.delete"
										class="danger"
										@click="memberAction(row, 'delete')"
									/>
								</template>
							</div>
						</td>
					</tr>
					<tr v-if="!table.rows.data.length">
						<td
							:colspan="table.columns.length + 1"
							class="!py-12 text-center admin-muted"
						>
							{{ labels.empty }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div
			v-if="table.rows.last_page"
			class="flex flex-wrap items-center justify-between gap-3 p-3 border-t border-[var(--admin-border)]"
		>
			<span class="admin-muted text-xs">
				{{ table.rows.from || 0 }}–{{ table.rows.to || 0 }} / {{ table.rows.total }}
			</span>
			<div class="admin-toolbar">
				<Link
					v-if="table.rows.prev_page_url"
					:href="table.rows.first_page_url"
					class="admin-button"
					preserve-scroll
					:aria-label="labels.first_page"
				>
					«
				</Link>
				<Link
					v-if="table.rows.prev_page_url"
					:href="table.rows.prev_page_url"
					class="admin-button"
					preserve-scroll
				>
					{{ labels.previous }}
				</Link>
				<span class="text-xs px-2">
					{{ table.rows.current_page }} / {{ table.rows.last_page }}
				</span>
				<Link
					v-if="table.rows.next_page_url"
					:href="table.rows.next_page_url"
					class="admin-button"
					preserve-scroll
				>
					{{ labels.next }}
				</Link>
				<Link
					v-if="table.rows.next_page_url"
					:href="table.rows.last_page_url"
					class="admin-button"
					preserve-scroll
					:aria-label="labels.last_page"
				>
					»
				</Link>
			</div>
		</div>
		<ActionDialog ref="dialog" />
	</section>
</template>

<script setup>
	import { computed, ref } from 'vue';
	import { Link, usePage } from '@inertiajs/vue3';
	import ActionDialog from './ActionDialog.vue';
	import ActionButton from './ActionButton.vue';
	import MessagePreview from './MessagePreview.vue';
	import Popper from '~/components/Popper.vue';
	const props = defineProps({ table: Object });
	const page = usePage();
	const labels = computed(() => page.props.admin.labels);
	const dialog = ref();
	const actionRows = computed(() =>
		props.table.rows.data.some((row) => row.action_url || row.queue_url || row.member_url),
	);
	const text = (value) =>
		value === null || value === undefined || value === ''
			? '—'
			: typeof value === 'boolean'
				? value
					? labels.value.yes
					: labels.value.no
				: value;
	const field = (key, type, options = []) => ({ key, type, options, label: labels.value[key] });
	function changeLevel(row) {
		dialog.value.open({
			title: labels.value.change_level + ' · ' + row.name,
			url: row.action_url,
			data: { level: row.level },
			fields: [field('level', 'number')],
		});
	}
	function queueAction(row, action) {
		dialog.value.open({
			title: labels.value[action] + ' · ' + row.name,
			url: row.queue_url,
			data: { action },
			danger: action === 'delete',
		});
	}
	function memberAction(row, action) {
		dialog.value.open({
			title: labels.value[action],
			url: row.member_url,
			data: { action, rank: row.rank ?? '' },
			fields: action === 'rank' ? [field('rank', 'select', row.ranks)] : [],
			danger: action === 'delete',
		});
	}
</script>
