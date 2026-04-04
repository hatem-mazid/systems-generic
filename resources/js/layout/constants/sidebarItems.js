export const SIDEBAR_TITLES = {
    Dashboard: "Sidebar.Dashboard",
    Categories: "Sidebar.Categories",
    Products: "Sidebar.Products",
    UnitGroups: "Sidebar.UnitGroups",
    UnitsManagement: "Sidebar.UnitsManagement",
    Users: "Sidebar.Users",
};

export const sidebarItems = [
    {
        icon: "pi pi-home",
        title: SIDEBAR_TITLES.Dashboard,
        to: "/",
        permission: null,
    },
    {
        icon: "pi pi-tags",
        title: SIDEBAR_TITLES.Categories,
        to: "/categories",
        permission: null,
    },
    {
        icon: "pi pi-shopping-bag",
        title: SIDEBAR_TITLES.Products,
        to: "/products",
        permission: null,
    },
    {
        icon: "pi pi-th-large",
        title: SIDEBAR_TITLES.UnitGroups,
        to: "/unit-groups-setup",
        permission: null,
    },
    {
        icon: "pi pi-table",
        title: SIDEBAR_TITLES.UnitsManagement,
        to: "/units-management",
        permission: null,
    },
    {
        icon: "pi pi-user",
        title: SIDEBAR_TITLES.Users,
        to: "/users",
        permission: "users index",
    },
];
