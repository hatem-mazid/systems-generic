import Aura from "@primeuix/themes/aura";
import { addIcons, OhVueIcon } from "oh-vue-icons";
import * as HiIcons from "oh-vue-icons/icons/hi/index.js";
import { MdTablerestaurantOutlined } from "oh-vue-icons/icons/md/index.js";
import { createPinia } from "pinia";
import PrimeVue from "primevue/config";
import StyleClass from "primevue/styleclass";
import Tooltip from "primevue/tooltip";
import ConfirmationService from "primevue/confirmationservice";
import ToastService from "primevue/toastservice";
import { createApp } from "vue";
import App from "./App.vue";
import AppIcon from "./components/common/AppIcon.vue";
import i18n from "./i18n/index";
import router from "./router";
import isRTL from "./utils/isRTL";

const app = createApp(App);
const pinia = createPinia();
addIcons(...Object.values(HiIcons));
addIcons(MdTablerestaurantOutlined);

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: ".dark",
        },
    },
});
app.use(i18n);
app.use(pinia);
app.use(router);
app.use(ConfirmationService);
app.use(ToastService);

app.directive("styleclass", StyleClass);
app.directive("tooltip", Tooltip);
app.component("v-icon", OhVueIcon);
app.component("AppIcon", AppIcon);

app.config.globalProperties.$isRTL = isRTL;
app.mount("#app");
