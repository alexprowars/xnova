<template>
	<Head :title="$t('pages.auth.recovery')"/>
	<RemindForm/>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import RemindForm from '~/components/Page/Index/RemindForm.vue';
</script>