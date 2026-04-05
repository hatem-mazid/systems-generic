import type { Order } from "../../../apis/services/orders/orders.type";

function escapeHtml(s: string): string {
    return String(s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function formatMoney(value: string | number | undefined): string {
    if (value === undefined || value === null) {
        return "—";
    }
    const n = typeof value === "string" ? parseFloat(value) : value;
    if (Number.isNaN(n)) {
        return String(value);
    }
    return n.toFixed(2);
}

export type InvoicePrintLabels = {
    title: string;
    unit: string;
    waiter: string;
    status: string;
    openedAt: string;
    createdAt: string;
    product: string;
    qty: string;
    price: string;
    lineTotal: string;
    total: string;
};

/**
 * Opens a print dialog with a minimal invoice layout for the given order.
 */
export function printOrderInvoice(order: Order, labels: InvoicePrintLabels): void {
    const w = window.open("", "_blank", "width=820,height=960");
    if (!w) {
        return;
    }

    const id = order.id ?? "—";
    const unit = escapeHtml(String(order.unit_name ?? order.unit_id ?? "—"));
    const user = escapeHtml(String(order.user_name ?? "—"));
    const status = escapeHtml(String(order.status ?? "—"));
    const opened = escapeHtml(
        order.opened_at ? formatDateTime(order.opened_at) : "—"
    );
    const created = escapeHtml(
        order.created_at ? formatDateTime(order.created_at) : "—"
    );
    const total = formatMoney(order.total);

    const rows =
        order.items?.length ?
            order.items
                .map((line) => {
                    const name = escapeHtml(String(line.name ?? "—"));
                    const qty = escapeHtml(String(line.quantity ?? "—"));
                    const price = formatMoney(line.price);
                    const lineTot = formatMoney(line.total);
                    return `<tr>
            <td>${name}</td>
            <td style="text-align:center">${qty}</td>
            <td style="text-align:right">${price}</td>
            <td style="text-align:right">${lineTot}</td>
          </tr>`;
                })
                .join("") :
            `<tr><td colspan="4" style="text-align:center;color:#666">—</td></tr>`;

    const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>${escapeHtml(labels.title)} #${id}</title>
  <style>
    body { font-family: system-ui, sans-serif; padding: 24px; color: #111; }
    h1 { font-size: 1.25rem; margin: 0 0 16px; }
    table.meta { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
    table.meta td { padding: 4px 8px; vertical-align: top; }
    table.meta td:first-child { font-weight: 600; width: 140px; }
    table.items { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    table.items th, table.items td { border: 1px solid #ccc; padding: 8px; }
    table.items th { background: #f4f4f4; text-align: left; }
    .total { margin-top: 16px; text-align: right; font-size: 1.1rem; font-weight: 700; }
    @media print { body { padding: 12px; } }
  </style>
</head>
<body>
  <h1>${escapeHtml(labels.title)} #${id}</h1>
  <table class="meta">
    <tr><td>${escapeHtml(labels.unit)}</td><td>${unit}</td></tr>
    <tr><td>${escapeHtml(labels.waiter)}</td><td>${user}</td></tr>
    <tr><td>${escapeHtml(labels.status)}</td><td>${status}</td></tr>
    <tr><td>${escapeHtml(labels.openedAt)}</td><td>${opened}</td></tr>
    <tr><td>${escapeHtml(labels.createdAt)}</td><td>${created}</td></tr>
  </table>
  <table class="items">
    <thead>
      <tr>
        <th>${escapeHtml(labels.product)}</th>
        <th style="text-align:center;width:72px">${escapeHtml(labels.qty)}</th>
        <th style="text-align:right;width:96px">${escapeHtml(labels.price)}</th>
        <th style="text-align:right;width:96px">${escapeHtml(labels.lineTotal)}</th>
      </tr>
    </thead>
    <tbody>${rows}</tbody>
  </table>
  <p class="total">${escapeHtml(labels.total)}: ${total}</p>
  <script>window.onload=function(){window.focus();window.print();}</script>
</body>
</html>`;

    w.document.open();
    w.document.write(html);
    w.document.close();
}

function formatDateTime(iso: string): string {
    try {
        const d = new Date(iso);
        return d.toLocaleString();
    } catch {
        return iso;
    }
}
