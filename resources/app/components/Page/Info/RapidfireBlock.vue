<template>
	<div v-if="rapidfire && (Object.keys(rapidfire['to']).length || Object.keys(rapidfire['from']).length)" class="block">
		<div class="title">{{ $t('pages.info.combat.rapidfire') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div v-if="Object.keys(rapidfire['to']).length" class="grid">
					<div class="th">
						<div v-for="(fCnt, fId) in rapidfire['to']">
							{{ $t('pages.info.combat.hits_fleet') }} <span style="color: #00ff00">{{ fCnt }}</span> {{ $t('pages.info.combat.hits_units') }} {{ $t('tech.'+ fId) }}
						</div>
					</div>
				</div>
				<div v-if="Object.keys(rapidfire['from']).length" class="grid">
					<div class="th">
						<div v-for="(fCnt, fId) in rapidfire['from']">
							{{ $t('tech.'+ fId) }} {{ $t('pages.info.combat.hits_fleet').toLowerCase() }} <span style="color: #ff0000">{{ fCnt }}</span> {{ $t('pages.info.combat.hits_units') }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
	defineProps({
		rapidfire: {
			type: Object
		}
	})
</script>