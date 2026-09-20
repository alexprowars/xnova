<template>
	<div class="editor-component">
		<div class="editor-component-toolbar">
			<span class="gensmall">
				<select name="btnSize" aria-label="Размер текста" @change="addTag('[size='+$event.target.options[$event.target.selectedIndex].value+']|[/size]')">
					<option value="9">Маленький</option>
					<option value="11" selected>Нормальный</option>
					<option value="20">Большой</option>
					<option value="25">Огромный</option>
				</select>
			</span>
			<button type="button" class="buttons button is-secondary icon-button" title="Жирный" @click="addTag('[b]|[/b]')">
				<BoldIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Курсив" @click="addTag('[i]|[/i]')">
				<ItalicIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Подчёркнутый" @click="addTag('[u]|[/u]')">
				<UnderlineIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Зачёркнутый" @click="addTag('[s]|[/s]')">
				<StrikeIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="По центру" @click="addTag('[center]|[/center]')">
				<AlignCenterIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="По левому краю" @click="addTag('[left]|[/left]')">
				<AlignLeftIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="По правому краю" @click="addTag('[right]|[/right]')">
				<AlignRightIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="По ширине" @click="addTag('[justify]|[/justify]')">
				<AlignJustifyIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Спойлер" @click="addTag('[spoiler=]|[/spoiler]')">
				<SpoilerIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="YOUTUBE" @click="addTag('[youtube]|[/youtube]', 2)">
				<VideoIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Вставить ссылку" @click="addTag('[url]|[/url]', 1)">
				<LinkIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Вставить картинку" @click="addTag('[img]|[/img]', 3)">
				<ImageIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Вставить большую картинку" @click="addTag('[img_big]|[/img_big]', 4)">
				<ImageLargeIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Нумерованый список" @click="addTag('[numlist]|[/numlist]', 5)">
				<NumberedListIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Список" @click="addTag('[list]|[/list]', 5)">
				<ListIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Цитата" @click="addTag('[quote]|[/quote]', 0)">
				<QuoteIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Цитата с автором" @click="addTag('[quote author=]|[/quote]', 0)">
				<QuoteAuthorIcon aria-hidden="true" focusable="false"/>
			</button>
			<Popper :triggers="['click']" :popper-triggers="['click']">
				<button type="button" class="buttons button is-secondary icon-button" title="Смайлы">
					<SmileIcon aria-hidden="true" focusable="false"/>
				</button>
				<template #content>
					<div class="smiles">
						<img v-for="smile in smilesList" :src="'/assets/images/smile/'+smile+'.gif'" :alt="smile" @click="addSmile(smile)">
					</div>
				</template>
			</Popper>
			<button type="button" class="buttons button is-secondary icon-button" title="Цвет текста" :aria-pressed="showColors" @click="showColors = !showColors">
				<ColorIcon aria-hidden="true" focusable="false"/>
			</button>
			<button type="button" class="buttons button is-secondary icon-button" title="Цвет фона" :aria-pressed="showBgColors" @click="showBgColors = !showBgColors">
				<BackgroundIcon aria-hidden="true" focusable="false"/>
			</button>

			<button v-if="value.length > 0" type="button" class="buttons button is-secondary icon-button" title="Предварительный просмотр" :aria-pressed="showPreview" @click="showPreview = !showPreview">
				<PreviewIcon aria-hidden="true" focusable="false"/>
			</button>
		</div>

		<div v-show="showColors" class="colorpicker">
			<button v-for="color in colors" :key="color" type="button" @click="addTag('[color=#'+color+']|[/color]')" :style="'background:#'+color" :title="'#' + color" :aria-label="'Цвет текста #' + color"></button>
		</div>

		<div v-show="showBgColors" class="colorpicker">
			<button v-for="color in colors" :key="color" type="button" @click="addTag('[bgcolor=#'+color+']|[/bgcolor]')" :style="'background:#'+color" :title="'#' + color" :aria-label="'Цвет фона #' + color"></button>
		</div>


		<textarea ref="textRef" :name="name" rows="10" v-model="value"></textarea>

		<div v-if="showPreview" class="editor-component-preview">
			<div class="editor-preview-title">Предварительный просмотр</div>
			<div class="editor-preview-body" v-html="parser.parse(value)"></div>
		</div>
	</div>
</template>

<script setup>
	import parser from '~/utils/parser.js';
	import { computed, ref } from 'vue';
	import Popper from './Popper.vue';
	import BoldIcon from '~/images/icons/editor/bold.svg?component';
	import ItalicIcon from '~/images/icons/editor/italic.svg?component';
	import UnderlineIcon from '~/images/icons/editor/underline.svg?component';
	import StrikeIcon from '~/images/icons/editor/strike.svg?component';
	import AlignCenterIcon from '~/images/icons/editor/align-center.svg?component';
	import AlignLeftIcon from '~/images/icons/editor/align-left.svg?component';
	import AlignRightIcon from '~/images/icons/editor/align-right.svg?component';
	import AlignJustifyIcon from '~/images/icons/editor/align-justify.svg?component';
	import SpoilerIcon from '~/images/icons/editor/spoiler.svg?component';
	import VideoIcon from '~/images/icons/editor/video.svg?component';
	import LinkIcon from '~/images/icons/editor/link.svg?component';
	import ImageIcon from '~/images/icons/editor/image.svg?component';
	import ImageLargeIcon from '~/images/icons/editor/image-large.svg?component';
	import NumberedListIcon from '~/images/icons/editor/numbered-list.svg?component';
	import ListIcon from '~/images/icons/editor/list.svg?component';
	import QuoteIcon from '~/images/icons/editor/quote.svg?component';
	import QuoteAuthorIcon from '~/images/icons/editor/quote-author.svg?component';
	import SmileIcon from '~/images/icons/editor/smile.svg?component';
	import ColorIcon from '~/images/icons/editor/color.svg?component';
	import BackgroundIcon from '~/images/icons/editor/background.svg?component';
	import PreviewIcon from '~/images/icons/editor/preview.svg?component';

	defineProps({
		name: String,
	});

	const value = defineModel();

	const textRef = ref();
	const showColors = ref(false);
	const showBgColors = ref(false);
	const showPreview = ref(false);
	const smilesList = ref(parser.patterns.smiles);

	const colors = computed(() => {
		let c = ['00', '33', '66', '99', 'cc', 'ff'];
		let colors = [];

		for (let r = 0; r < 6; r++) {
			for (let g = 0; g < 6; g++) {
				for (let b = 0; b < 6; b++) {
					colors.push(c[r] + c[g] + c[b]);
				}
			}
		}

		return colors;
	});

	function addSmile (smile) {
		value.value = value.value + ' :' + smile + ':';
	}

	function addTag (tag, type) {
		let len 	= value.value.length;
		let start 	= textRef.value.selectionStart;
		let end 	= textRef.value.selectionEnd;

		let rep = parser.addTag(tag, value.value.substring(start, end), type)

		value.value = value.value.substring(0, start) + rep + value.value.substring(end, len);
	}
</script>