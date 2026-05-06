<template>
	<Head :title="data['name']"/>
	<div class="tech_view" ref="elementRef"></div>
</template>

<script setup>
	import { ECOTree } from '~/utils/techtree'
	import { onMounted, useTemplateRef } from 'vue';
	import { Head } from '@inertiajs/vue3';

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

	let counter = 0;
	let objectTree;
	const elementRef = useTemplateRef('elementRef');

	onMounted(() => {
		objectTree = new ECOTree('objectTree', elementRef.value);
		createTree(1, -1, props.data['id'], props.data['level'], props.data['available']);

		window.objectTree = objectTree;
	});

	function createTree (tid, prntid, element, level, access, fwrd) {
		let item = props.data['items'].find((el) => el.id === element);

		let active = 'lime';

		if (!access) {
			active = 'red';
		}

		if (element !== -1) {
			objectTree.add(tid, prntid, '<div class="tch_tx_nmcont"><span class="tch_tx_name">' + item.name + '</span></div><img id="tch_img_' + tid + '" name="' + item['name'] + '" src="'+'/assets/images/elements/' + item.id + '.webp" class="tch_icon_' + active + '"><div class="tch_tx_lvl">' + level + '</div>', null, null, active, active, active);
		} else {
			objectTree.add(tid, prntid, '<div class="tch_tx_nmcont"><span class="tch_tx_name">' + fwrd + '</span></div><img id="tch_img_' + tid + '" src="skins/sn_space_blue/images/pixel.png" class="tch_icon_' + active + '"><div class="tch_tx_lvl">' + level + '</div>', null, null, active, active, active);
		}

		counter++;
		objectTree.UpdateTree();

		if (!access) {
			document.querySelector('#tch_img_' + prntid)?.classList.add('tch_icon_red');
		}

		if (element !== -1 && item['requirments'].length) {
			for (let req of item['requirments']) {
				let actclr = 'positive';

				if (req['current'] < req['level']) {
					actclr = 'negative';
				}

				let lvtmp = '';

				if (req['queue'] !== -1) {
					lvtmp = '<span class="' + actclr + '">' + req['current'] + '</span><span style="color:gold"> + ' + req['queue'] + '</span>/<span class="positive">' + req['level'] + '</span>';
				} else {
					lvtmp = '<span class="' + actclr + '">' + req['current'] + '</span>/<span class="positive">' + req['level'] + '</span>';
				}

				let fwrld = '';

				if (req['id'] === -1) {
					fwrld = req['name'];
				}

				createTree(counter + 1, tid, req['id'], lvtmp, req['current'] >= req['level'], fwrld);
			}
		}

		objectTree.UpdateTree();
	}
</script>