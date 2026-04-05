import type { AxiosResponse } from "axios";
import http from "../../http";
import { Items } from "../../types";
import { Order } from "./orders.type";

export const ordersService = {
    getOrders: async (params?: {
        page?: number;
        per_page?: number;
        status?: string | null;
        user_id?: number | string | null;
        /** ISO date string `YYYY-MM-DD` */
        date_from?: string | null;
        /** ISO date string `YYYY-MM-DD` */
        date_to?: string | null;
    }): Promise<AxiosResponse<Items<Order>>> => {
        return http.get<Items<Order>>("/api/orders", { params });
    },
    getOrder: async (
        id: string | number
    ): Promise<AxiosResponse<Order>> => {
        return http.get<Order>(`/api/orders/${id}`);
    },
};
