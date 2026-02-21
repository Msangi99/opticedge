const XLSX = require('xlsx');
const workbook = XLSX.readFile('../Opticedge dev.xlsx');
const sheetNames = workbook.SheetNames;

console.log("Sheets found:", sheetNames);

sheetNames.forEach(sheetName => {
    console.log(`\n--- Sheet: ${sheetName} ---`);
    const worksheet = workbook.Sheets[sheetName];
    // Get range to determine dimensions
    const range = XLSX.utils.decode_range(worksheet['!ref']);
    console.log(`Dimensions: Rows ${range.s.r + 1} to ${range.e.r + 1}, Cols ${range.s.c + 1} to ${range.e.c + 1}`);

    // Dump first 10 rows to see headers and structure
    const json = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: "" });
    console.log(JSON.stringify(json.slice(0, 10), null, 2));
});
