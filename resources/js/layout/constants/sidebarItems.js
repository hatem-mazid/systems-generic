export const SIDEBAR_TITLES = {
    Dashboard: "Sidebar.Dashboard",
    Categories: "Sidebar.Categories",
    Products: "Sidebar.Products",
    UnitGroups: "Sidebar.UnitGroups",
    UnitsManagement: "Sidebar.UnitsManagement",
    Orders: "Sidebar.Orders",
    Reports: "Sidebar.Reports",
    ReportsExpenses: "Sidebar.ReportsExpenses",
    Expenses: "Sidebar.Expenses",
    Settings: "Sidebar.Settings",
    Users: "Sidebar.Users",
    Roles: "Sidebar.Roles",
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
        permission: "categories index",
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
        permission: "unit-groups index",
    },
    {
        icon: "hi-table",
        title: SIDEBAR_TITLES.UnitsManagement,
        to: "/units-management",
        permission: "units index",
    },
    {
        icon: "hi-receipt-tax",
        title: SIDEBAR_TITLES.Orders,
        to: "/orders",
        permission: "order index",
    },
    {
        icon: "hi-currency-dollar",
        title: SIDEBAR_TITLES.Expenses,
        to: "/expenses",
        permission: "expenses index",
    },
    {
        icon: "hi-chart-bar",
        title: SIDEBAR_TITLES.Reports,
        to: "/reports/orders",
        permission: "view reports",
    },
    {
        icon: "hi-chart-pie",
        title: SIDEBAR_TITLES.ReportsExpenses,
        to: "/reports/expenses",
        permission: "view reports",
    },
    {
        icon: "hi-cog",
        title: SIDEBAR_TITLES.Settings,
        to: "/settings",
        permission: "users index",
    },
    {
        icon: "hi-users",
        title: SIDEBAR_TITLES.Users,
        to: "/users",
        permission: "users index",
    },
    {
        icon: "hi-shield-check",
        title: SIDEBAR_TITLES.Roles,
        to: "/roles",
        permission: "roles index",
    },
];
