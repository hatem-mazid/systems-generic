import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        component: () => import("./pages/index.vue"),
    },
    {
        path: "/users",
        component: () => import("./pages/users/index.vue"),
    },
    {
        path: "/users/create",
        component: () => import("./pages/users/create.vue"),
    },
    {
        path: "/users/:id",
        component: () => import("./pages/users/[id].vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
