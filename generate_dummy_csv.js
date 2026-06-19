const fs = require('fs');

const products = {
    'Americano': 20000,
    'French Fries': 25000,
    'Nasi Goreng DOM': 40000,
    'Ayam Chili Padi': 38000,
    'Lychee Tea': 22000,
    'Matcha Latte': 30000,
    'Mix Platter': 35000,
    'Spaghetti Aglio Olio': 45000,
    'Caramel Macchiato': 32000,
    'Kopi Susu DOM': 25000,
    'Zafeer Milktea': 28000
};

const productNames = Object.keys(products);

function getRandomDate(start, end) {
    const date = new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()));
    const pad = (n) => n.toString().padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

let csvContent = 'receipt_no,trx_date,product_name,qty,subtotal\n';

let trxCount = 1;
const totalTargetRows = 800; // Let's aim for ~800 rows
let currentRowCount = 0;

const startDate = new Date(2026, 0, 1);
const endDate = new Date(2026, 5, 10);

while (currentRowCount < totalTargetRows) {
    // Generate a random date for the transaction
    const trxDate = getRandomDate(startDate, endDate);
    const receiptNo = `TRX-20260610-${trxCount.toString().padStart(4, '0')}`;
    
    // Determine how many items in this transaction (1 to 5)
    const itemsInTrx = Math.floor(Math.random() * 5) + 1;
    
    for (let i = 0; i < itemsInTrx; i++) {
        if (currentRowCount >= totalTargetRows) break;
        
        const productName = productNames[Math.floor(Math.random() * productNames.length)];
        const qty = Math.floor(Math.random() * 4) + 1;
        const price = products[productName];
        const subtotal = qty * price;
        
        // Quote product name if it contains spaces
        const formattedProductName = productName.includes(' ') ? `"${productName}"` : productName;
        
        csvContent += `${receiptNo},"${trxDate}",${formattedProductName},${qty},${subtotal}\n`;
        currentRowCount++;
    }
    trxCount++;
}

fs.writeFileSync('presentation_dummy_data.csv', csvContent);
console.log(`Generated presentation_dummy_data.csv with ${currentRowCount} rows.`);
