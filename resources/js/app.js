import { createApp } from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura';
import router from './router'
import i18n from './i18n/index'
import StyleClass from 'primevue/styleclass';
import { createPinia } from 'pinia'

import isRTL from './utils/isRTL';
import 'primeicons/primeicons.css'

const app = createApp(App)
const pinia = createPinia()

app.use(PrimeVue, {
	theme: {
		preset: Aura,
		options: {
			darkModeSelector: '.dark',
		}
	},
})
app.use(i18n);
app.use(router);
app.use(pinia);

app.directive('styleclass', StyleClass);

app.config.globalProperties.$isRTL = isRTL;

app.mount('#app')
