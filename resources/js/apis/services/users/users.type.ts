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
    password_confirmation?: string;
    role?: UserRole | string;
    active?: boolean;
}
