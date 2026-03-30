export enum UserRole {
    ADMIN = "admin",
    Accountant = "accounting",
    Waiter = "waiter",
}

export interface User {
    id?: string;
    name?: string;
    email?: string;
    password?: string;
    role?: UserRole;
    active?: boolean;
}
