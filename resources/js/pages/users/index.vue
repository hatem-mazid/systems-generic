<template>
    <div class="">
        <div class="flex justify-between">
            <h1 class="text-2xl text-surface-800 font-semibold">
                {{ $t('Users') }}
            </h1>

            <Button size="lg" :label="$t('Add User')" icon="pi pi-plus" />
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4">
            <Card class="p-4 border rounded-lg border-surface-300 dark:border-surface-700">
                <ul class="divide-y divide-surface-200 dark:divide-surface-700">
                    <li v-for="user in users" :key="user.id" class="py-2 flex items-center gap-4">
                        <Avatar :image="user.avatar" shape="circle" />
                        <div>
                            <p class="text-sm text-surface-800 font-medium">{{ user.name }}</p>
                            <p class="text-xs text-surface-500">{{ user.email }}</p>
                        </div>
                    </li>
                </ul>
            </Card>

        </div>
    </div>
</template>


<script setup>
import { onMounted, ref } from 'vue';
import { Button } from 'primevue';
import axios from 'axios';

const users = ref([]);

const fetchUsers = () => {
    axios.get('/api/users')
        .then(response => {
            users.value = response.data;
        })
        .catch(error => {
            console.error('Error fetching users:', error);
        });
};

onMounted(() => {
    fetchUsers();
});

</script>
