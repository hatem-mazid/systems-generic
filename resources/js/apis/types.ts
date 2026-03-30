export type ApiResponse<T> = {
    data: T;
};

export type Items<T> = {
    items: T[];
    meta: {
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
};
