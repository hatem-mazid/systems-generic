import { createRouter, createWebHistory } from 'vue-router'

import home from './pages/index.vue'
import userIndex from './pages/users/index.vue'

const routes = [
    { path: '/', component: home },
    { path: '/users', component: userIndex },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
