// composables/useRTL.ts
import { ref, computed } from 'vue';

const isRTL = ref(false);

export const useRTL = () => {
	const checkRTL = () => {
		const lang = localStorage.getItem('vectorian-palace-lang');
		return lang === 'ar';
	};

	// Update RTL state
	const updateRTLState = () => {
		isRTL.value = checkRTL();
	};

	// Computed property to get RTL state
	const rtlState = computed(() => isRTL.value);

	return {
		isRTL: rtlState,
		updateRTLState
	};
};
