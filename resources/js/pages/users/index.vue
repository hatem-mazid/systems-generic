<template>
    <div class="">
        <div class="flex justify-between">
            <h1 class="text-2xl text-surface-800 font-semibold">
                {{ $t('Users') }}
            </h1>

            <Button size="lg" :label="$t('Add User')" icon="pi pi-plus" />
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4">
            <Skeleton v-if="isLoading" v-for="_ in paginator.per_page" width="100%" height="150px" class="mb-2" />

            <Card v-else v-for="user in users" :key="user.id" class="border rounded-lg border-surface-300 dark:border-surface-700">
                <template #content>
                    <div class="flex justify-center mb-2">
                        <Avatar :label="user.name[0]" size="xlarge" shape="circle" />
                    </div>
                    <div>
                        <p class="text-base text-surface-800 font-medium">{{ user.name }}</p>
                        <p class="text-sm text-surface-500">{{ user.email }}</p>
                        <p class="text-sm text-surface-500">{{ user.roles[0].name }}</p>
                    </div>

                    <div class="flex gap-2 mt-4 justify-center">
                        <Button variant="outlined" size="large" rounded icon="pi pi-pencil" severity="info" />
                        <Button variant="outlined" size="large" rounded icon="pi pi-trash" severity="danger" />
                    </div>
                </template>
            </Card>
        </div>

        <Paginator
            class="mt-5 bg-transparent"
            :rows="paginator.per_page"
            :totalRecords="paginator.total"
            :first="(paginator.current_page - 1) * paginator.per_page"
            @page="onPageChange"
        />
    </div>
</template>


<script setup>
import { onMounted, ref } from 'vue';
import { Button } from 'primevue';
import axios from 'axios';

const isLoading = ref(true);
const users = ref([]);
const paginator = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
});

const fetchUsers = (page = 1) => {
    isLoading.value = true;
    axios.get('/api/users', { params: { page, per_page: paginator.value.per_page } })
        .then(({ data }) => {
            users.value = data.users.data;
            paginator.value = data.users;
        })
        .catch(error => {
            console.error('Error fetching users:', error);
        })
        .finally(() => {
            isLoading.value = false;
        });
};

const onPageChange = (event) => {
    // event.page is 0-indexed, event.rows is the new per_page
    paginator.value.per_page = event.rows;
    fetchUsers(event.page + 1);
};

onMounted(() => {
    fetchUsers();
});
</script>
