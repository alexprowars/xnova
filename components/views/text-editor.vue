<template>
	<div class="editor-component">
		<div class="editor-component-toolbar">
			<span class="gensmall">
				<select name="btnSize" @change="addTag('[size='+$event.target.options[$event.target.selectedIndex].value+']|[/size]')">
					<option value="9">Маленький</option>
					<option value="11" selected>Нормальный</option>
					<option value="20">Большой</option>
					<option value="25">Огромный</option>
				</select>
			</span>
			<button type="button" class="buttons" title="Жирный" @click="addTag('[b]|[/b]')">
				<span class="sprite bb_text_bold"></span>
			</button>
			<button type="button" class="buttons" title="Курсив" @click="addTag('[i]|[/i]')">
				<span class="sprite bb_text_italic"></span>
			</button>
			<button type="button" class="buttons" title="Подчёркнутый" @click="addTag('[u]|[/u]')">
				<span class="sprite bb_text_underline"></span>
			</button>
			<button type="button" class="buttons" title="Зачёркнутый" @click="addTag('[s]|[/s]')">
				<span class="sprite bb_text_strikethrough"></span>
			</button>
			<button type="button" class="buttons" title="По центру" @click="addTag('[center]|[/center]')">
				<span class="sprite bb_text_align_center"></span>
			</button>
			<button type="button" class="buttons" title="По левому краю" @click="addTag('[left]|[/left]')">
				<span class="sprite bb_text_align_left"></span>
			</button>
			<button type="button" class="buttons" title="По правому краю" @click="addTag('[right]|[/right]')">
				<span class="sprite bb_text_align_right"></span>
			</button>
			<button type="button" class="buttons" title="По ширине" @click="addTag('[justify]|[/justify]')">
				<span class="sprite bb_text_align_justify"></span>
			</button>
			<button type="button" class="buttons" title="Спойлер" @click="addTag('[spoiler=]|[/spoiler]')">
				<span class="sprite bb_eye"></span>
			</button>
			<button type="button" class="buttons" title="YOUTUBE" @click="addTag('[youtube]|[/youtube]', 2)">
				<span class="sprite bb_film_add"></span>
			</button>
			<button type="button" class="buttons" title="Вставить ссылку" @click="addTag('[url]|[/url]', 1)">
				<span class="sprite bb_world_link"></span>
			</button>
			<button type="button" class="buttons" title="Вставить картинку" @click="addTag('[img]|[/img]', 3)">
				<span class="sprite bb_picture_add"></span>
			</button>
			<button type="button" class="buttons" title="Вставить большую картинку" @click="addTag('[img_big]|[/img_big]', 4)">
				<span class="sprite bb_image_add"></span>
			</button>
			<button type="button" class="buttons" title="Нумерованый список" @click="addTag('[numlist]|[/numlist]', 5)">
				<span class="sprite bb_text_list_numbers"></span>
			</button>
			<button type="button" class="buttons" title="Список" @click="addTag('[list]|[/list]', 5)">
				<span class="sprite bb_text_list_bullets"></span>
			</button>
			<button type="button" class="buttons" title="Цитата" @click="addTag('[quote]|[/quote]', 0)">
				<span class="sprite bb_text_signature"></span>
			</button>
			<button type="button" class="buttons" title="Цитата" @click="addTag('[quote author=]|[/quote]', 0)">
				<span class="sprite bb_user_comment"></span>
			</button>
			<button type="button" class="buttons" title="Смайлы" @click="showSmiles = !showSmiles">
				<span class="sprite bb_emoticon_grin"></span>
			</button>
			<button type="button" class="buttons" title="Цвет текста" @click="showColors = !showColors">
				<span class="sprite bb_color_swatch"></span>
			</button>
			<button type="button" class="buttons" title="Цвет фона" @click="showBgColors = !showBgColors">
				<span class="sprite bb_palette"></span>
			</button>

			<span v-if="message.length > 0" class="buttons" title="Предварительный просмотр" @click="showPreview = !showPreview">
				<span class="sprite bb_tick"></span>
			</span>
		</div>

		<div v-show="showColors" class="colorpicker">
			<span v-for="color in colors" @click="addTag('[color=#'+color+']|[/color]')" :style="'background:#'+color">&nbsp;</span>
	    </div>

		<div v-show="showBgColors" class="colorpicker">
			<span v-for="color in colors" @click="addTag('[bgcolor=#'+color+']|[/bgcolor]')" :style="'background:#'+color">&nbsp;</span>
	    </div>

		<div v-if="showSmiles" class="smiles">
			<img v-for="smile in smilesList" :src="'/images/smile/'+smile+'.gif'" :alt="smile" @click="addSmile(smile)">
		</div>
		
		<textarea name="text" ref="text" rows="10" v-model="message" v-on:input="update"></textarea>

		<div v-if="showPreview" class="editor-component-preview table">
			<div class="row">
				<div class="col-12 c">Предварительный просмотр</div>
			</div>
			<div class="row">
				<div class="col-12 b" v-html="parser.parse(message)"></div>
			</div>
		</div>
	</div>
</template>

<script>
	import parser from '../../utils/parser.js'

	export default {
		name: "text-editor",
		props: {
			text: {
				default: '',
				type: String
			},
			value: {
				default: '',
				type: String
			}
		},
		data () {
			return {
				message: '',
				showColors: false,
				showBgColors: false,
				showSmiles: false,
				showPreview: false,
				smilesList: parser.patterns.smiles
			}
		},
		computed: {
			colors ()
			{
				let c = ['00', '33', '66', '99', 'cc', 'ff'];
				let colors = [];

				for (let r = 0; r < 6; r++)
				{
					for (let g = 0; g < 6; g++)
					{
						for (let b = 0; b < 6; b++)
							colors.push(c[r] + c[g] + c[b]);
					}
				}

				return colors;
			}
		},
		watch: {
			message (val) {
				this.$emit('update:text', val)
			},
			text (val) {
				this.message = val
			},
			value (val) {
				this.message = val
			}
		},
		methods:
		{
			addSmile (smile) {
				this.message = this.message+' :'+smile+':';
			},

			addTag (tag, type)
			{
				let len 	= this.message.length;
				let start 	= this.$refs.text.selectionStart;
				let end 	= this.$refs.text.selectionEnd;

				let rep = parser.addTag(tag, this.message.substring(start, end), type)

				this.message = this.message.substring(0, start) + rep + this.message.substring(end, len);
			},
			update () {
				this.$emit('input', this.message);
			}
		},
		created ()
		{
			if (this.value.length > 0)
				this.text = this.value
			else
				this.message = this.text
		}
	}
</script>