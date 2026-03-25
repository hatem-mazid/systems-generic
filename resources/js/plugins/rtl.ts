// plugins/rtl.ts
import { App } from 'vue';
import { useRTL } from '../composables/useRTL';

export const rtlPlugin = {
	install: (app: App) => {
		const { isRTL, updateRTLState } = useRTL();

		// Add global property
		app.config.globalProperties.$isRTL = isRTL;

		// Add to provide/inject API
		app.provide('isRTL', isRTL);

		// Initialize RTL state
		updateRTLState();

		// Add global method to check RTL
		app.config.globalProperties.$checkRTL = () => isRTL.value;
	}
};
