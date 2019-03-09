import Vue from 'vue'

import ErrorMessage from './../components/views/message.vue'
import Tab from './../components/views/tab.vue'
import Tabs from './../components/views/tabs.vue'
import Pagination from './../components/views/pagination.vue'
import TextViewer from './../components/views/text-viewer.vue'
import TextEditor from './../components/views/text-editor.vue'
import Number from './../components/views/number.vue'
import Chat from './../components/views/chat.vue'
import Colored from './../components/views/colored.vue'
import PopupLink from './../components/views/popup-link.vue'
import Timer from './../components/views/timer.vue'
import PlanetLink from './../components/views/planet-link.vue'
import RouterForm from './../components/views/router-form.vue'

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
Vue.component('planet-link', PlanetLink);
Vue.component('router-form', RouterForm);