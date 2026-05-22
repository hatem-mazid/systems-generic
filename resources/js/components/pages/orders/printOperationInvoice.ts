import {
    formatReceiptDate,
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

const THERMAL_OPERATION_EXTRA_CSS = `
  .section-title {
    margin-top: 2mm;
    font-size: 12px;
    font-weight: 600;
  }
  .item-qty {
    flex-shrink: 0;
    white-space: nowrap;
    font-weight: 700;
  }
  .item-notes {
    margin-top: 0.6mm;
    font-size: 9px;
    color: #555;
    font-style: italic;
  }
  .receipt[dir="rtl"] .item-notes {
    text-align: right;
  }
  .receipt[dir="ltr"] .item-notes {
    text-align: left;
  }
`;

export type OperationInvoiceItem = {
    name?: string | null;
    quantity?: number | string | null;
    notes?: string | null;
};

export type OperationInvoiceSection = {
    section_name?: string | null;
    section_code?: string | null;
    items?: OperationInvoiceItem[];
};

export type OperationPrintLabels = {
    tableOrTakeaway: string;
    orderNo: string;
    batchNo: string;
    time: string;
    date: string;
    sectionTitle: string;
    locale?: string;
};

export type OperationPrintOrder = {
    id?: number | string | null;
    unit_id?: number | string | null;
    unit_name?: string | null;
    opened_at?: string | null;
    created_at?: string | null;
};

export function getOperationReceiptTimestamp(order: OperationPrintOrder | null | undefined): string | null {
    return order?.opened_at ?? order?.created_at ?? null;
}

/**
 * Opens a print dialog with a thermal POS operation invoice for a station section.
 */
export function printOperationInvoice(
    section: OperationInvoiceSection,
    labels: OperationPrintLabels,
): void {
    const w = window.open("", "_blank", "width=320,height=720");
    if (!w) {
        return;
    }

    const locale = labels.locale ?? "en";
    const dir = locale === "ar" ? "rtl" : "ltr";
    const businessName = escapeHtml(getReceiptBusinessName());
    const sectionTitle = escapeHtml(labels.sectionTitle);
    const tableOrTakeaway = escapeHtml(labels.tableOrTakeaway);
    const orderNo = escapeHtml(labels.orderNo);
    const batchNo = escapeHtml(labels.batchNo);
    const time = escapeHtml(labels.time);
    const date = escapeHtml(labels.date);

    const items = section?.items ?? [];
    const itemsHtml =
        items.length ?
            items
                .map((line) => {
                    const name = escapeHtml(String(line?.name ?? "—"));
                    const qty = escapeHtml(String(line?.quantity ?? "—"));
                    const notes =
                        line?.notes ?
                            `<div class="item-notes">${escapeHtml(String(line.notes))}</div>` :
                            "";
                    return `<div class="item">
            <div class="item-head">
              <span class="item-name">${name}</span>
              <span class="item-qty">x${qty}</span>
            </div>
            ${notes}
          </div>`;
                })
                .join("") :
            `<div class="empty-items">—</div>`;

    const html = `<!DOCTYPE html>
<html lang="${escapeHtml(locale)}" dir="${dir}">
<head>
  <meta charset="utf-8" />
  <title>${sectionTitle}</title>
  <style>${THERMAL_RECEIPT_CSS}${THERMAL_OPERATION_EXTRA_CSS}</style>
</head>
<body>
  <div class="receipt" dir="${dir}">
    <header class="brand">
      <div class="business">${businessName}</div>
      <div class="section-title">${sectionTitle}</div>
      <div class="table-no">${tableOrTakeaway}</div>
    </header>

    <div class="meta-grid">
      <div class="meta-box">${orderNo}</div>
      <div class="meta-box">${batchNo}</div>
      <div class="meta-box">${time}</div>
      <div class="meta-box">${date}</div>
    </div>

    <div class="items-list">${itemsHtml}</div>
  </div>
  <script>window.onload=function(){window.focus();window.print();}</script>
</body>
</html>`;

    w.document.open();
    w.document.write(html);
    w.document.close();
}

export function buildOperationPrintLabels(
    order: OperationPrintOrder | null | undefined,
    batchNo: number | null,
    section: OperationInvoiceSection,
    t: (key: string, params?: Record<string, unknown>) => string,
    locale: string,
): OperationPrintLabels {
    const timestamp = getOperationReceiptTimestamp(order);
    const unitLabel =
        order?.unit_id == null ?
            t("OrdersList.Statuses.takeaway") :
            t("OrdersList.ReceiptTableNo", {
                n: order?.unit_name ?? order?.unit_id ?? "—",
            });

    return {
        tableOrTakeaway: unitLabel,
        orderNo: t("OrdersList.ReceiptOrderNo", { n: order?.id ?? "—" }),
        batchNo:
            batchNo != null ?
                t("OrderDetail.BatchLabel", { n: batchNo }) :
                t("OrderDetail.BatchOpenLabel"),
        time: t("OrdersList.ReceiptTime", {
            time: formatReceiptTime(timestamp, locale),
        }),
        date: t("OrdersList.ReceiptDate", {
            date: formatReceiptDate(timestamp, locale),
        }),
        sectionTitle:
            section?.section_name || section?.section_code || t("OrderDetail.ItemSection"),
        locale,
    };
}
