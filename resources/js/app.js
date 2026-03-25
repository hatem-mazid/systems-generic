import { createApp } from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura';
import router from './router'
import i18n from './i18n/index'
import StyleClass from 'primevue/styleclass';

import 'primeicons/primeicons.css'

import { rtlPlugin } from './plugins/rtl';

const app = createApp(App)

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
app.use(rtlPlugin);

app.directive('styleclass', StyleClass);

app.mount('#app')
