const XLSX = require('xlsx');
const workbook = XLSX.readFile('../Opticedge dev.xlsx');
const sheetNames = workbook.SheetNames;

sheetNames.forEach(sheetName => {
    console.log(`\n--- Sheet: ${sheetName} ---`);
    const worksheet = workbook.Sheets[sheetName];
    const json = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: "" });
    // Print first 20 rows
    console.log(JSON.stringify(json.slice(0, 20), null, 2));
});
