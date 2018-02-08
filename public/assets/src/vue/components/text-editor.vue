<template>
	<div class="editor-component">
		<div class="editor-component-toolbar">
			<span class="gensmall">
				<select name="btnSize" v-on:change="addTag('[size='+$event.target.options[$event.target.selectedIndex].value+']|[/size]')">
					<option value="9">Маленький</option>
					<option value="11" selected>Нормальный</option>
					<option value="20">Большой</option>
					<option value="25">Огромный</option>
				</select>
			</span>
			<button type="button" class="buttons" title="Жирный" v-on:click="addTag('[b]|[/b]')">
				<span class="sprite bb_text_bold"></span>
			</button>
			<button type="button" class="buttons" title="Курсив" v-on:click="addTag('[i]|[/i]')">
				<span class="sprite bb_text_italic"></span>
			</button>
			<button type="button" class="buttons" title="Подчёркнутый" v-on:click="addTag('[u]|[/u]')">
				<span class="sprite bb_text_underline"></span>
			</button>
			<button type="button" class="buttons" title="Зачёркнутый" v-on:click="addTag('[s]|[/s]')">
				<span class="sprite bb_text_strikethrough"></span>
			</button>
			<button type="button" class="buttons" title="По центру" v-on:click="addTag('[center]|[/center]')">
				<span class="sprite bb_text_align_center"></span>
			</button>
			<button type="button" class="buttons" title="По левому краю" v-on:click="addTag('[left]|[/left]')">
				<span class="sprite bb_text_align_left"></span>
			</button>
			<button type="button" class="buttons" title="По правому краю" v-on:click="addTag('[right]|[/right]')">
				<span class="sprite bb_text_align_right"></span>
			</button>
			<button type="button" class="buttons" title="По ширине" v-on:click="addTag('[justify]|[/justify]')">
				<span class="sprite bb_text_align_justify"></span>
			</button>
			<button type="button" class="buttons" title="Спойлер" v-on:click="addTag('[spoiler=]|[/spoiler]')">
				<span class="sprite bb_eye"></span>
			</button>
			<button type="button" class="buttons" title="YOUTUBE" v-on:click="addTag('[youtube]|[/youtube]', 2)">
				<span class="sprite bb_film_add"></span>
			</button>
			<button type="button" class="buttons" title="Вставить ссылку" v-on:click="addTag('[url]|[/url]', 1)">
				<span class="sprite bb_world_link"></span>
			</button>
			<button type="button" class="buttons" title="Вставить картинку" v-on:click="addTag('[img]|[/img]', 3)">
				<span class="sprite bb_picture_add"></span>
			</button>
			<button type="button" class="buttons" title="Вставить песню" v-on:click="addTag('[mp3]|[/mp3]', 6)">
				<span class="sprite bb_sound_add"></span>
			</button>
			<button type="button" class="buttons" title="Вставить большую картинку" v-on:click="addTag('[img_big]|[/img_big]', 4)">
				<span class="sprite bb_image_add"></span>
			</button>
			<button type="button" class="buttons" title="Нумерованый список" v-on:click="addTag('[numlist]|[/numlist]', 5)">
				<span class="sprite bb_text_list_numbers"></span>
			</button>
			<button type="button" class="buttons" title="Список" v-on:click="addTag('[list]|[/list]', 5)">
				<span class="sprite bb_text_list_bullets"></span>
			</button>
			<button type="button" class="buttons" title="Цитата" v-on:click="addTag('[quote]|[/quote]', 0)">
				<span class="sprite bb_text_signature"></span>
			</button>
			<button type="button" class="buttons" title="Цитата" v-on:click="addTag('[quote author=]|[/quote]', 0)">
				<span class="sprite bb_user_comment"></span>
			</button>
			<button type="button" class="buttons" title="Смайлы" v-on:click="showSmiles = !showSmiles">
				<span class="sprite bb_emoticon_grin"></span>
			</button>
			<button type="button" class="buttons" title="Цвет текста" v-on:click="showColors = !showColors">
				<span class="sprite bb_color_swatch"></span>
			</button>
			<button type="button" class="buttons" title="Цвет фона" v-on:click="showBgColors = !showBgColors">
				<span class="sprite bb_palette"></span>
			</button>

			<span class="buttons" title="Предварительный просмотр" v-on:click="showPreview = !showPreview">
				<span class="sprite bb_tick"></span>
			</span>
		</div>

		<div v-show="showColors" id="colorpicker" class="colorpicker">
			<span v-for="color in colors" v-on:click="addTag('[color=#'+color+']|[/color]')" :style="'background:#'+color">&nbsp;</span>
	    </div>

		<div v-show="showBgColors" id="colorpicker2" class="colorpicker">
			<span v-for="color in colors" v-on:click="addTag('[bgcolor=#'+color+']|[/bgcolor]')" :style="'background:#'+color">&nbsp;</span>
	    </div>

		<div v-if="showSmiles" id="smiles" class="colorpicker">
			<img v-for="smile in parser.patterns.smiles" :src="$root.getUrl('assets/images/smile/'+smile+'.gif')" :alt="smile" v-on:click="addSmile(smile)" style="cursor:pointer">
		</div>
		
		<textarea name="text" ref="text" rows="10" title="" v-model="message"></textarea>

		<div v-if="showPreview" id="showpanel">
			<table class="table">
				<tr>
					<td class="c"><b>Предварительный просмотр</b></td>
				</tr>
				<tr>
					<td class="b" v-html="parser.parse(message)"></td>
				</tr>
			</table>
		</div>
	</div>
</template>

<script>
	export default {
		name: "text-editor",
		props: {
			text: {
				default: '',
				type: String
			}
		},
		data () {
			return {
				message: this.text,
				showColors: false,
				showBgColors: false,
				showSmiles: false,
				showPreview: false,
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
		methods:
		{
			addSmile (smile) {
				this.message = this.message+' :'+smile+':';
			},

			addTag (tag, type)
			{
				if (typeof type === 'undefined')
					type = 0;

				let tags = tag.split('|');

				let openTag = tags[0];
				let closeTag = tags[1];

				let rep, url;

				if (type === 1)
					url = prompt('Введите ссылку:','');
				else if (type === 2)
					url = prompt('Введите ссылку на видео:','');
				else if (type === 3 || type === 4)
					url = prompt('Введите ссылку на картинку:','');
				else if (type === 6)
					url = prompt('Введите ссылку на песню:','');

				if (type > 0 && type <= 6 && (url === '' || url === null))
					return;

				let len 	= this.message.length;
				let start 	= this.$refs.text.selectionStart;
				let end 	= this.$refs.text.selectionEnd;

				let sel = this.message.substring(start, end);

				if (type === 0)
					rep = openTag + sel + closeTag;
				else if (type === 1)
				{
					if (sel === "")
						rep = '[url]' + url + '[/url]';
					else
						rep = '[url=' + url + ']' + sel + '[/url]';
				}
				else if (type === 2)
					rep = '[youtube]'  + url + '[/youtube]';
				else if (type === 3)
					rep = '[img]'  + url + '[/img]';
				else if (type === 4)
					rep = '[img_big]'  + url + '[/img_big]';
				else if (type === 5)
				{
					let list = sel.split('\n');

					for (let i = 0;i < list.length; i++)
						list[i] = '[*]' + list[i] + '[/*]';

					rep = openTag + '\n' + list.join("\n") + '\n' +closeTag;
				}
				else if (type === 6)
					rep = '[mp3]'  + url + '[/mp3]';

				this.message = this.message.substring(0, start) + rep + this.message.substring(end, len);
			}
		}
	}
</script>