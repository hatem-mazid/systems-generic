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
    {
        path: "/categories",
        component: () => import("./pages/categories/index.vue"),
    },
    {
        path: "/categories/create",
        component: () => import("./pages/categories/create.vue"),
    },
    {
        path: "/categories/:id",
        component: () => import("./pages/categories/[id].vue"),
    },
    {
        path: "/products",
        component: () => import("./pages/products/index.vue"),
    },
    {
        path: "/products/create",
        component: () => import("./pages/products/create.vue"),
    },
    {
        path: "/products/:id(\\d+)",
        component: () => import("./pages/products/[id].vue"),
    },
    {
        path: "/unit-groups-setup",
        component: () => import("./pages/unitGroups/index.vue"),
    },
    {
        path: "/unit-groups-setup/create",
        component: () => import("./pages/unitGroups/create.vue"),
    },
    {
        path: "/unit-groups-setup/:id",
        component: () => import("./pages/unitGroups/[id].vue"),
    },
    {
        path: "/units-management",
        component: () => import("./pages/unitsManagement/index.vue"),
    },
    {
        path: "/orders",
        component: () => import("./pages/orders/index.vue"),
    },
    {
        path: "/orders/:id",
        component: () => import("./pages/orders/[id].vue"),
    },
    {
        path: "/orders/:id/invoice",
        component: () => import("./pages/orders/invoice.vue"),
    },
    {
        path: "/reservations/create",
        component: () => import("./pages/reservations/create.vue"),
    },
    {
        path: "/units/create",
        component: () => import("./pages/units/create.vue"),
    },
    {
        path: "/units/transfer",
        component: () => import("./pages/units/transfer.vue"),
    },
    {
        path: "/units/:id",
        component: () => import("./pages/units/[id].vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
