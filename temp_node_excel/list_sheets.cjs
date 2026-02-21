const XLSX = require('xlsx');
const workbook = XLSX.readFile('../Opticedge dev.xlsx');
console.log("ALL SHEETS:", workbook.SheetNames);
