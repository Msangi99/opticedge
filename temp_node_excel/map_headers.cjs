const XLSX = require('xlsx');
const workbook = XLSX.readFile('Opticedge dev.xlsx'); // Correct path
const sheet = workbook.Sheets[workbook.SheetNames[0]];

const json = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: "" });

// Rows 0 and 1 seem to be headers (based on previous output inspection)
// Row 1 (Index 1): General Categories (Shop, Zenon, etc.)
// Row 2 (Index 2): Specific Columns (Stock, Sales, etc.)

const row1 = json[1];
const row2 = json[2];

const columns = [];

for (let i = 0; i < row2.length; i++) {
    let category = row1[i] || "";
    // If category is empty, look back for the nearest non-empty one (merged cells behavior)
    if (!category && i > 0) {
        // Simple lookback approach for now, but row1 might be sparse
        // Actually, merged cells are stored in !merges, but visual scanning of row1 is often enough if we track 'currentCategory'
    }

    // Better approach: track current category
}

let currentCategory = "General";
const mappedColumns = [];

for (let i = 0; i < row2.length; i++) {
    if (row1[i]) {
        currentCategory = row1[i];
    }

    const subColumn = row2[i];
    if (subColumn) {
        mappedColumns.push({
            index: i,
            category: currentCategory,
            name: subColumn
        });
    }
}

console.log(JSON.stringify(mappedColumns, null, 2));
