import Vue from 'vue';
import Cookies from 'js-cookie';
import ElementUI from 'element-ui';
import App from './views/App';
import store from './store';
import router from '@/router';
import i18n from './lang'; // Internationalization
import '@/icons'; // icon
import '@/permission'; // permission control
import '@fortawesome/fontawesome-free/css/all.css'
import '@fortawesome/fontawesome-free/js/all.js'
import VueClipboard from 'vue-clipboard2'
import { VueReCaptcha } from 'vue-recaptcha-v3'

import * as filters from './filters'; // global filters
import VueEvents from 'vue-events'
import VueLazyload from 'vue-lazyload'
 

Vue.use(VueReCaptcha, { siteKey: '6LeNueQUAAAAAOdXr5rhWWz5f4KodsexOwonkLAp' })

Vue.use(VueEvents)
Vue.use(VueLazyload)

VueClipboard.config.autoSetContainer = true // add this line
Vue.use(VueClipboard)

Vue.use(ElementUI, {
  size: Cookies.get('size') || 'medium', // set element-ui default size
  i18n: (key, value) => i18n.t(key, value),
});

// register global utility filters.
Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key]);
});

Vue.config.productionTip = false;

new Vue({
  el: '#app',
  router,
  store,
  i18n,
  render: h => h(App),
});
