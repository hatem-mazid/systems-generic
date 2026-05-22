import type { Order } from "../../../apis/services/orders/orders.type";
import { mergeOrderItems } from "../../../utils/orderItemsMerge";
import {
    formatReceiptDate,
    formatReceiptMoney,
    formatReceiptTime,
    getReceiptBusinessName,
    THERMAL_RECEIPT_CSS,
} from "../../../utils/thermalReceipt";

function escapeHtml(s: string): string {
    return String(s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

export type InvoicePrintLabels = {
    tableNo: string;
    orderNo: string;
    cashier: string;
    time: string;
    date: string;
    qtyPrice: string;
    total: string;
    locale?: string;
};

/**
 * Opens a print dialog with a thermal POS receipt layout for the given order.
 */
export function printOrderInvoice(order: Order, labels: InvoicePrintLabels): void {
    const w = window.open("", "_blank", "width=320,height=720");
    if (!w) {
        return;
    }

    const locale = labels.locale ?? "en";
    const dir = locale === "ar" ? "rtl" : "ltr";
    const id = order.id ?? "—";
    const unit = String(order.unit_name ?? order.unit_id ?? "—");
    const user = String(order.user_name ?? "—");
    const timestamp = order.opened_at ?? order.created_at ?? null;
    const businessName = escapeHtml(getReceiptBusinessName());

    const tableNo = escapeHtml(labels.tableNo.replace("{n}", String(unit)));
    const orderNo = escapeHtml(labels.orderNo.replace("{n}", String(id)));
    const cashier = escapeHtml(labels.cashier.replace("{name}", user));
    const time = escapeHtml(
        labels.time.replace("{time}", formatReceiptTime(timestamp, locale)),
    );
    const date = escapeHtml(
        labels.date.replace("{date}", formatReceiptDate(timestamp, locale)),
    );
    const totalLine = escapeHtml(
        labels.total.replace("{amount}", formatReceiptMoney(order.total, locale)),
    );

    const merged = mergeOrderItems(order.items);
    const qtyPriceTpl = labels.qtyPrice;
    const itemsHtml =
        merged.length ?
            merged
                .map((line) => {
                    const name = escapeHtml(String(line.name ?? "—"));
                    const price = formatReceiptMoney(line.price, locale);
                    const lineTot = formatReceiptMoney(line.total, locale);
                    const detail = escapeHtml(
                        qtyPriceTpl
                            .replace("{qty}", String(line.quantity ?? "—"))
                            .replace("{price}", price),
                    );
                    return `<div class="item">
            <div class="item-head">
              <span class="item-name">${name}</span>
              <span class="item-total">${escapeHtml(lineTot)}</span>
            </div>
            <div class="item-detail">${detail}</div>
          </div>`;
                })
                .join("") :
            `<div class="empty-items">—</div>`;

    const html = `<!DOCTYPE html>
<html lang="${escapeHtml(locale)}" dir="${dir}">
<head>
  <meta charset="utf-8" />
  <title>${orderNo}</title>
  <style>${THERMAL_RECEIPT_CSS}</style>
</head>
<body>
  <div class="receipt" dir="${dir}">
    <header class="brand">
      <div class="business">${businessName}</div>
      <div class="table-no">${tableNo}</div>
    </header>

    <div class="meta-grid">
      <div class="meta-box">${orderNo}</div>
      <div class="meta-box">${cashier}</div>
      <div class="meta-box">${time}</div>
      <div class="meta-box">${date}</div>
    </div>

    <div class="items-list">${itemsHtml}</div>

    <div class="total-box">${totalLine}</div>
  </div>
  <script>window.onload=function(){window.focus();window.print();}</script>
</body>
</html>`;

    w.document.open();
    w.document.write(html);
    w.document.close();
}
