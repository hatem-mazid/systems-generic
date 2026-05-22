function m(t,i="en"){if(t==null||t==="")return"—";const e=typeof t=="string"?parseFloat(t):Number(t);if(Number.isNaN(e))return String(t);const n=i==="ar"?"ar-IQ":void 0,r=new Intl.NumberFormat(n,{minimumFractionDigits:0,maximumFractionDigits:0}).format(e);return i==="ar"?`${r} د.ع`:`${r} IQD`}function a(t,i="en"){if(!t)return"—";try{const e=new Date(t);return i==="ar"?e.toLocaleDateString("ar-IQ",{year:"numeric",month:"numeric",day:"numeric"}):`${e.getFullYear()}/${e.getMonth()+1}/${e.getDate()}`}catch{return String(t)}}function o(t,i="en"){if(!t)return"—";try{return new Date(t).toLocaleTimeString(i==="ar"?"ar-IQ":void 0,{hour:"2-digit",minute:"2-digit"})}catch{return String(t)}}function g(){return"Vectorian Palace Cafe"}const s=`
  @page { size: 80mm auto; margin: 0; }
  html, body {
    width: 80mm;
    margin: 0;
    padding: 0;
    background: #fff;
  }
  .receipt {
    width: 72mm;
    margin: 0 auto;
    padding: 3mm 2mm 4mm;
    font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
    font-size: 11px;
    line-height: 1.4;
    color: #000;
  }
  .brand {
    text-align: center;
    margin-bottom: 3mm;
  }
  .business {
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  .table-no {
    margin-top: 2mm;
    font-size: 12px;
    font-weight: 600;
  }
  .meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2mm;
    margin-bottom: 4mm;
  }
  .meta-box {
    border: 1px solid #bbb;
    border-radius: 6px;
    padding: 2mm 1.5mm;
    font-size: 10px;
    text-align: center;
    line-height: 1.35;
    word-break: break-word;
  }
  .items-list {
    margin-bottom: 3mm;
  }
  .item {
    margin-bottom: 3.5mm;
  }
  .item-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 2mm;
    font-weight: 700;
    font-size: 11px;
  }
  .item-name {
    flex: 1;
    text-align: inherit;
    word-break: break-word;
  }
  .item-total {
    flex-shrink: 0;
    white-space: nowrap;
  }
  .item-detail {
    margin-top: 0.6mm;
    font-size: 10px;
    color: #333;
  }
  .receipt[dir="rtl"] .item-detail {
    text-align: right;
  }
  .receipt[dir="ltr"] .item-detail {
    text-align: left;
  }
  .empty-items {
    padding: 3mm 0;
    text-align: center;
    color: #666;
    font-size: 10px;
  }
  .total-box {
    border: 1px solid #333;
    border-radius: 10px;
    padding: 3mm 2mm;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
  }
`;export{s as T,o as a,m as b,a as f,g};
