import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";

export type ExpenseUserRef = {
    id: number;
    name: string;
};

export type Expense = {
    id: number;
    description: string;
    amount: number;
    type: string;
    expense_date: string;
    expense_by: ExpenseUserRef | null;
    created_by: ExpenseUserRef | null;
    created_at?: string;
    updated_at?: string;
};

export type ExpenseWritePayload = {
    description: string;
    amount: number;
    type: string;
    expense_date: string;
    expense_by_id: number | null;
};

export type ExpenseUserOption = { id: number; name: string };

export const expensesService = {
    getUserOptions: async (): Promise<
        AxiosResponse<{ items: ExpenseUserOption[] }>
    > => {
        return http.get<{ items: ExpenseUserOption[] }>(
            "/api/expenses/user-options"
        );
    },

    getExpenses: async (params?: {
        page?: number;
        per_page?: number;
        type?: string | null;
        date_from?: string | null;
        date_to?: string | null;
    }): Promise<AxiosResponse<Items<Expense>>> => {
        return http.get<Items<Expense>>("/api/expenses", { params });
    },

    getExpense: async (id: string): Promise<AxiosResponse<Expense>> => {
        return http.get<Expense>(`/api/expenses/${id}`);
    },

    createExpense: async (
        payload: ExpenseWritePayload
    ): Promise<AxiosResponse<Expense>> => {
        return http.post<Expense>("/api/expenses", payload);
    },

    updateExpense: async (
        id: string,
        payload: Partial<ExpenseWritePayload>
    ): Promise<AxiosResponse<Expense>> => {
        return http.put<Expense>(`/api/expenses/${id}`, payload);
    },
};
