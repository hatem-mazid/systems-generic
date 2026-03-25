import { defineStore } from 'pinia'
import { ref } from 'vue';

export const useUserStore = defineStore('user',() => {
	const info = ref({});
	const permissions = ref([]);

	const setInfo = (i) => {
		info.value = i;
	}

	const setPermissions = (arr) => {
		permissions.value = arr;
	}

	const hasPermission = (permission) => {
		if(!permission) return true;

		if(Array.isArray(permission)) {
			return permissions.value.some(e => permission.includes(e));
		}

		return permissions.value.includes(permission);
	};

	return {
		info,
		permissions,
		setPermissions,
		setInfo,
		hasPermission
	}
})
