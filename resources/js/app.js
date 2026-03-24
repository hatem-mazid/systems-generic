import { createApp } from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura';
import router from './router'
import i18n from './i18n/index'
import 'primeicons/primeicons.css'


const app = createApp(App)

app.use(PrimeVue, {
    theme: {
        preset: Aura,
    }
})
app.use(i18n)
app.use(router)

app.mount('#app')
