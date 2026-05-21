import type { Order } from "../../../apis/services/orders/orders.type";
import { mergeOrderItems } from "../../../utils/orderItemsMerge";

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
    return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: "IQD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(n);
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

const THERMAL_STYLES = `
  @page { size: 80mm auto; margin: 0; }
  html, body {
    width: 80mm;
    margin: 0;
    padding: 0;
    background: #fff;
  }
  .receipt {
    width: 76mm;
    margin: 0 auto;
    padding: 2mm 1mm 3mm;
    font-family: "Courier New", Courier, monospace;
    font-size: 10px;
    line-height: 1.35;
    color: #000;
  }
  .title {
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    letter-spacing: 0.02em;
  }
  .subtitle {
    margin-top: 1mm;
    font-size: 9px;
    text-align: center;
  }
  .divider {
    margin: 2mm 0;
    border-top: 1px dashed #000;
  }
  .row {
    display: flex;
    justify-content: space-between;
    gap: 2mm;
    margin-bottom: 0.8mm;
    font-size: 9px;
  }
  .row span:last-child {
    font-weight: 700;
    text-align: right;
    word-break: break-word;
  }
  table.items {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
    table-layout: fixed;
  }
  table.items th {
    padding: 1mm 0;
    border-bottom: 1px solid #000;
    font-weight: 700;
    text-align: left;
  }
  table.items td {
    padding: 1.2mm 0;
    vertical-align: top;
  }
  table.items .name { width: 38%; word-break: break-word; }
  table.items .qty { width: 12%; text-align: center; }
  table.items .price,
  table.items .total-col { width: 25%; text-align: right; white-space: nowrap; }
  .empty { padding: 2mm 0; text-align: center; }
  .total {
    display: flex;
    justify-content: space-between;
    gap: 2mm;
    font-size: 12px;
    font-weight: 700;
  }
`;

/**
 * Opens a print dialog with a thermal POS receipt layout for the given order.
 */
export function printOrderInvoice(order: Order, labels: InvoicePrintLabels): void {
    const w = window.open("", "_blank", "width=320,height=720");
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

    const merged = mergeOrderItems(order.items);
    const rows =
        merged.length ?
            merged
                .map((line) => {
                    const name = escapeHtml(String(line.name ?? "—"));
                    const qty = escapeHtml(String(line.quantity ?? "—"));
                    const price = formatMoney(line.price);
                    const lineTot = formatMoney(line.total);
                    return `<tr>
            <td class="name">${name}</td>
            <td class="qty">${qty}</td>
            <td class="price">${price}</td>
            <td class="total-col">${lineTot}</td>
          </tr>`;
                })
                .join("") :
            `<tr><td colspan="4" class="empty">—</td></tr>`;

    const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>${escapeHtml(labels.title)} #${id}</title>
  <style>${THERMAL_STYLES}</style>
</head>
<body>
  <div class="receipt">
    <div class="title">${escapeHtml(labels.title)} #${id}</div>
    <div class="subtitle">${created}</div>

    <div class="divider"></div>

    <div class="row"><span>${escapeHtml(labels.unit)}</span><span>${unit}</span></div>
    <div class="row"><span>${escapeHtml(labels.waiter)}</span><span>${user}</span></div>
    <div class="row"><span>${escapeHtml(labels.status)}</span><span>${status}</span></div>
    <div class="row"><span>${escapeHtml(labels.openedAt)}</span><span>${opened}</span></div>

    <div class="divider"></div>

    <table class="items">
      <thead>
        <tr>
          <th class="name">${escapeHtml(labels.product)}</th>
          <th class="qty">${escapeHtml(labels.qty)}</th>
          <th class="price">${escapeHtml(labels.price)}</th>
          <th class="total-col">${escapeHtml(labels.lineTotal)}</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
    </table>

    <div class="divider"></div>

    <div class="total">
      <span>${escapeHtml(labels.total)}</span>
      <span>${total}</span>
    </div>
  </div>
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
