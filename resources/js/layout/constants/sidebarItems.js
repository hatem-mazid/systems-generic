export const SIDEBAR_TITLES = {
    Dashboard: "Sidebar.Dashboard",
    Categories: "Sidebar.Categories",
    Products: "Sidebar.Products",
    UnitGroups: "Sidebar.UnitGroups",
    UnitsManagement: "Sidebar.UnitsManagement",
    Orders: "Sidebar.Orders",
    Users: "Sidebar.Users",
};

export const sidebarItems = [
    {
        icon: "hi-home",
        title: SIDEBAR_TITLES.Dashboard,
        to: "/",
        permission: null,
    },
    {
        icon: "hi-tag",
        title: SIDEBAR_TITLES.Categories,
        to: "/categories",
        permission: null,
    },
    {
        icon: "hi-shopping-bag",
        title: SIDEBAR_TITLES.Products,
        to: "/products",
        permission: null,
    },
    {
        icon: "hi-view-grid",
        title: SIDEBAR_TITLES.UnitGroups,
        to: "/unit-groups-setup",
        permission: null,
    },
    {
        icon: "hi-table",
        title: SIDEBAR_TITLES.UnitsManagement,
        to: "/units-management",
        permission: null,
    },
    {
        icon: "hi-receipt-tax",
        title: SIDEBAR_TITLES.Orders,
        to: "/orders",
        permission: "order index",
    },
    {
        icon: "hi-users",
        title: SIDEBAR_TITLES.Users,
        to: "/users",
        permission: "users index",
    },
];
