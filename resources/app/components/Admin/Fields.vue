<template>
	<div
		class="grid gap-x-5 gap-y-4"
		:class="compact ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3'"
	>
		<div
			v-for="field in fields"
			:key="field.key"
			class="admin-field"
			:class="{
				'md:col-span-2 xl:col-span-3':
					['textarea', 'checkboxes'].includes(field.type) && !compact,
			}"
		>
			<label
				:id="`${id}-label-${field.key}`"
				:for="field.type === 'checkboxes' ? undefined : `${id}-field-${field.key}`"
			>
				{{ field.label }}
			</label>
			<textarea
				:id="`${id}-field-${field.key}`"
				v-if="field.type === 'textarea'"
				v-model="form[field.key]"
				rows="6"
				:aria-invalid="Boolean(form.errors?.[field.key])"
			/>
			<div
				v-else-if="field.type === 'checkboxes'"
				class="admin-checkbox-group"
				role="group"
				:aria-labelledby="`${id}-label-${field.key}`"
			>
				<label
					v-for="option in field.options"
					:key="option.value"
					class="admin-checkbox-option"
				>
					<input v-model="form[field.key]" type="checkbox" :value="option.value" />
					<span>{{ option.label }}</span>
				</label>
			</div>
			<select
				v-else-if="field.type === 'select'"
				:id="`${id}-field-${field.key}`"
				v-model="form[field.key]"
			>
				<option value="">—</option>
				<option v-for="option in field.options" :key="option.value" :value="option.value">
					{{ option.label }}
				</option>
			</select>
			<input
				:id="`${id}-field-${field.key}`"
				v-else-if="field.type === 'checkbox'"
				v-model="form[field.key]"
				type="checkbox"
			/>
			<input
				:id="`${id}-field-${field.key}`"
				v-else-if="field.type === 'file'"
				type="file"
				accept="image/png,image/jpeg,image/webp"
				@change="form[field.key] = $event.target.files[0] || null"
			/>
			<input
				:id="`${id}-field-${field.key}`"
				v-else
				v-model="form[field.key]"
				:type="field.type"
				:step="field.type === 'number' ? 'any' : undefined"
				:autocomplete="field.type === 'password' ? 'new-password' : 'off'"
				:aria-invalid="Boolean(form.errors?.[field.key])"
			/>
			<small v-if="form.errors?.[field.key]" class="admin-error">
				{{ form.errors[field.key] }}
			</small>
		</div>
	</div>
</template>

<script setup>
	import { useId } from 'vue';

	defineProps({ fields: Array, form: Object, compact: Boolean });
	const id = useId();
</script>
