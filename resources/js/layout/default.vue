<template>
  <div class="default-layout">
		<!-- TODO lang: based on dir -->
		<sidebar v-model:minimize="minimize" :is-modal="isModal" v-model:show="show" />


		<div :class="[isModal || !show ? 'ps-0' : 'ps-[318px]' , 'min-h-screen flex flex-col transition-all']">
			<div class="p-4">
				<Menubar :dark="false">
					<template #start>
						<span v-if="isModal || !show">
							<Button type="button" variant="text" @click="collapse" icon="pi pi-bars" rounded outlined></Button>
						</span>
					</template>
					<!-- <template #item="{ item, props, hasSubmenu, root }">
						<a v-ripple class="flex items-center" v-bind="props.action">
							<span>{{ item.label }}</span>
							<Badge v-if="item.badge" :class="{ 'ml-auto': !root, 'ml-2': root }" :value="item.badge" />
							<span v-if="item.shortcut" class="ml-auto border border-surface rounded bg-emphasis dark:tex text-muted-color text-xs p-1">{{ item.shortcut }}</span>
							<i v-if="hasSubmenu" :class="['pi pi-angle-down ml-auto', { 'pi-angle-down': root, 'pi-angle-right': !root }]"></i>
						</a>
					</template> -->
					<template #end>
						<div class="flex items-center gap-2">
							<Button severity="secondary" @click="toggleLangMenu" rounded icon="pi pi-language" aria-label="Save" />
							<Menu ref="langMenu" :model="langItems" :popup="true">
								<template #item="{ item, props }">
									<a v-ripple class="flex items-center rounded" :class="{'bg-primary': locale == item.key}" v-bind="props.action">
										<span>{{ item.label }}</span>
									</a>
								</template>
							</Menu>

							<Button severity="secondary" @click="toggleDarkMode" rounded icon="pi pi-sun" aria-label="Save" />

							<Avatar role="button" :label="userStore.info?.name && userStore.info?.name[0] " class="!size-10" @click="toggleProfileMenu" style="background-color: #ece9fc; color: #2a1261" shape="circle" />
							<Menu ref="profileMenu" class="w-full md:w-60" id="overlay_menu" :model="profileMenuItems" :popup="true">
								<template #start>
									<button v-ripple class="relative gap-2 items-center overflow-hidden w-full border-0 bg-transparent flex p-2 pl-4 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-none cursor-pointer transition-colors duration-200">

										<Avatar role="button" label="V" @click="toggleProfileMenu" style="background-color: #ece9fc; color: #2a1261" shape="circle" />
										<span class="inline-flex flex-col items-start">
											<span class="font-bold">{{ userStore.info?.name }}</span>
										</span>
									</button>
								</template>
								<template #item="{ item, props }">
									<a v-ripple class="flex items-center" v-bind="props.action">
										<span :class="item.icon" />
										<span>{{ item.label }}</span>
										<Badge v-if="item.badge" class="ml-auto" :value="item.badge" />
										<span v-if="item.shortcut" class="ml-auto border border-surface rounded bg-emphasis text-muted-color text-xs p-1">{{ item.shortcut }}</span>
									</a>
								</template>
							</Menu>


						</div>
					</template>
				</Menubar>
			</div>
			<div class="flex-grow flex flex-col justify-between">
				<main class="p-4">
					<slot />
				</main>

				<footer class="">
					<div class="p-4 border-t border-surface-700 text-surface-500 dark:text-surface-400">
						© 2025 developed by spark team
					</div>
				</footer>
			</div>

		</div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import sidebar from './components/sidebar.vue';
import Menubar from 'primevue/menubar';
import { useUserStore } from '../stores/user';

import { useI18n } from 'vue-i18n';
// import { useUserStore } from "@/stores/user";
import { useRTL } from "../composables/useRTL";
import { useRouter, useRoute } from 'vue-router';
import { useWindowSize } from '@vueuse/core'

import axios from 'axios';
const userStore = useUserStore();

const { width } = useWindowSize();
const router = useRouter();
// const { info, logout } = useUserStore();

const minimize = ref(false);
const show = ref(width.value > 1024);
const isModal = ref(width.value < 1024);
const { t, locale } = useI18n();
const { updateRTLState } = useRTL();

const collapse = () => {
	show.value = !show.value;
};

function toggleDarkMode() {
	localStorage.setItem('vectorian-palace-theme', document.documentElement.classList.contains('dark'));
	document.documentElement.classList.toggle('dark');
}

const profileMenu = ref();
const profileMenuItems = ref([
	{
		separator: true
	},
	{
		label: 'Documents',
		items: [
			{
				label: 'New',
				icon: 'pi pi-plus',
				shortcut: '⌘+N'
			},
			{
				label: 'Search',
				icon: 'pi pi-search',
				shortcut: '⌘+S'
			}
		]
	},
	{
        label: 'Profile',
        items: [
            {
                label: 'Settings',
                icon: 'pi pi-cog',
                shortcut: '⌘+O',
            },
            {
                label: 'Messages',
                icon: 'pi pi-inbox',
                badge: 2
            },
            {
                label: 'Logout',
                icon: 'pi pi-sign-out',
                shortcut: '⌘+Q',
                command: () => { window.location.href = '/logout' }
            }
        ]
	},
]);

const toggleProfileMenu = (event) => {
	profileMenu.value.toggle(event);
};

const langMenu = ref(null);
const toggleLangMenu = (event) => {
	langMenu.value.toggle(event);
};
const langItems = ref([
	{
		label: 'English',
		command: () => {
			locale.value = 'en';
			document.getElementsByTagName('html')[0].setAttribute('dir', 'ltr');
			localStorage.setItem('vectorian-palace-lang', 'en');
			updateRTLState();
		},
		key: 'en',
	},
	{
		label: 'عربي',
		command: () => {
			locale.value = 'ar';
			document.getElementsByTagName('html')[0].setAttribute('dir', 'rtl');
			localStorage.setItem('vectorian-palace-lang', 'ar');
			updateRTLState();
		},
		key: 'ar',
	}
]);

const fetchUserInfo = async () => {
    try {
        const response = await axios.get('/user');
        userStore.setInfo(response.data);
    } catch (error) {
        console.error('Failed to fetch user info:', error);
    }
};

onMounted(() => {
    fetchUserInfo();

	if (localStorage.getItem('vectorian-palace-theme') === 'true') {
		document.documentElement.classList.remove('dark');
	} else {
		document.documentElement.classList.add('dark');
	}

	let savedLang = localStorage.getItem('vectorian-palace-lang');
	if (savedLang) {
		locale.value = savedLang;

		if(savedLang == 'ar') {
			document.getElementsByTagName('html')[0].setAttribute('dir', 'rtl')
		}
	}
});

watch(width, newVal => {
	isModal.value = newVal < 1024;
	show.value = newVal > 1024;
});

</script>
