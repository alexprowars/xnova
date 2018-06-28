import Vue from 'vue'

import ErrorMessage from './../views/message.vue'
import Tab from './tab.vue'
import Tabs from './tabs.vue'
import Pagination from './pagination.vue'
import TextViewer from './text-viewer.vue'
import TextEditor from './text-editor.vue'
import Number from './number.vue'
import Chat from './chat.vue'
import Colored from './colored.vue'
import PopupLink from './popup-link.vue'
import Timer from './timer.vue'

Vue.component('error-message', ErrorMessage)
Vue.component('tab', Tab);
Vue.component('tabs', Tabs);
Vue.component('pagination', Pagination);
Vue.component('text-viewer', TextViewer);
Vue.component('text-editor', TextEditor);
Vue.component('number', Number);
Vue.component('chat', Chat);
Vue.component('colored', Colored);
Vue.component('popup-link', PopupLink);
Vue.component('timer', Timer);