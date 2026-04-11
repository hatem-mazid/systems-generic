import type { AxiosResponse } from "axios";
import http from "../../http";

export type OrderReportGroupBy = "day" | "week" | "month";

export type OrderReportSeriesRow = {
    period: string;
    total_value: number;
    order_count: number;
};

export type OrderReportResponse = {
    group_by: OrderReportGroupBy;
    date_from: string;
    date_to: string;
    series: OrderReportSeriesRow[];
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
};
