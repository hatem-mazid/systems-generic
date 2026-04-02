export const SIDEBAR_TITLES = {
    Dashboard: "Sidebar.Dashboard",
    Categories: "Sidebar.Categories",
    UnitGroups: "Sidebar.UnitGroups",
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
        icon: "pi pi-th-large",
        title: SIDEBAR_TITLES.UnitGroups,
        to: "/unit-groups-setup",
        permission: null,
    },
    {
        icon: "pi pi-user",
        title: SIDEBAR_TITLES.Users,
        to: "/users",
        permission: "users index",
    },
];
