import Aura from "@primeuix/themes/aura";
import { createPinia } from "pinia";
import "primeicons/primeicons.css";
import PrimeVue from "primevue/config";
import StyleClass from "primevue/styleclass";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import ToastService from "primevue/toastservice";
import { createApp } from "vue";
import App from "./App.vue";
import i18n from "./i18n/index";
import router from "./router";
import isRTL from "./utils/isRTL";

const app = createApp(App);
const pinia = createPinia();

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: ".dark",
        },
    },
});
app.use(i18n);
app.use(router);
app.use(pinia);
app.use(ConfirmationService);
app.use(ToastService);

app.directive("styleclass", StyleClass);
app.directive("tooltip", Tooltip);

app.config.globalProperties.$isRTL = isRTL;
app.mount("#app");
