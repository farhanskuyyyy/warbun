const receipt = document.querySelector('[data-receipt-id]');
if (receipt) {
    const width = document.getElementById('receiptWidth');
    width.addEventListener('change', () => {
        receipt.dataset.paper = width.value;
        try { localStorage.setItem('warbun_receipt_paper', width.value); } catch { /* Printing still uses the selected width. */ }
    });
    document.getElementById('printReceiptButton').addEventListener('click', () => window.print());
    if (receipt.dataset.autoPrint === 'true') {
        const printOnce = () => {
            const key = 'warbun_print_opened_' + receipt.dataset.receiptId;
            try {
                if (sessionStorage.getItem(key)) return;
                sessionStorage.setItem(key, 'true');
            } catch { /* Manual reprinting remains available without browser storage. */ }
            window.print();
        };
        if (document.readyState === 'complete') printOnce();
        else window.addEventListener('load', printOnce, {once: true});
    }
}
