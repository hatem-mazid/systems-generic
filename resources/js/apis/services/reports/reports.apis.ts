import type { AxiosResponse } from "axios";
import http from "../../http";

export type OrderReportGroupBy = "day" | "week" | "month";

export type OrderReportSeriesRow = {
    period: string;
    total_value: number;
    order_count: number;
};

export type OrderReportUnitGroupRow = {
    unit_group_id: number | null;
    group_name: string;
    is_takeaway: boolean;
    total_value: number;
    order_count: number;
};

export type OrderReportResponse = {
    group_by: OrderReportGroupBy;
    date_from: string;
    date_to: string;
    series: OrderReportSeriesRow[];
    unit_group_breakdown: OrderReportUnitGroupRow[];
};

export type ExpenseReportSeriesRow = {
    period: string;
    total_amount: number;
    expense_count: number;
};

export type ExpenseReportResponse = {
    group_by: OrderReportGroupBy;
    date_from: string;
    date_to: string;
    series: ExpenseReportSeriesRow[];
};

export const reportsService = {
    getOrdersReport: async (params?: {
        group_by?: OrderReportGroupBy;
        date_from?: string | null;
        date_to?: string | null;
    }): Promise<AxiosResponse<OrderReportResponse>> => {
        return http.get<OrderReportResponse>("/api/reports/orders", {
            params,
        });
    },

    getExpensesReport: async (params?: {
        group_by?: OrderReportGroupBy;
        date_from?: string | null;
        date_to?: string | null;
    }): Promise<AxiosResponse<ExpenseReportResponse>> => {
        return http.get<ExpenseReportResponse>("/api/reports/expenses", {
            params,
        });
    },
};
