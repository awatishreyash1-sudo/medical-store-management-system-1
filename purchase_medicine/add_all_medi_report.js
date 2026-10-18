function printRecords() {
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #2eb8b8;">Purchase Medicine Report</h2>
            
        </div>`;

    // Select the table to print
    const tableContent = document.querySelector('.table-responsive').innerHTML;

    // Open new print window
    const printWindow = window.open('', '', 'width=1000,height=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(customHeader);
    printWindow.document.write(tableContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}