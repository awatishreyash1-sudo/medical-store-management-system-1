
// Medicine Row Add करने वाला Function
function addMedicineRow() {
    const table = document.getElementById("medicine_table").getElementsByTagName('tbody')[0];
    const row = table.insertRow();
    const srNo = table.rows.length;

    row.innerHTML = `
        <td width="60px"><input type="number" name="sr_no[]" class="form-control" value="${srNo}" readonly></td>
        <td style="position: relative;">
            <input type="text" class="form-control medicine_search" placeholder="Search Medicine" onkeyup="searchMedicine(this)">
            <div class="dropdown-menu medicine_dropdown"></div>
            <input type="hidden" name="product[]" class="product_id">
        </td>
        <td><input type="text" name="batch[]" class="form-control batch"></td>
        <td><input type="text" name="gst[]" class="form-control gst" oninput="calculateAmount(this.closest('tr'))"></td>
        <td><input type="text" name="mrp[]" class="form-control mrp" oninput="calculateAmount(this.closest('tr'))"></td>
        <td><input type="date" name="ex_date[]" class="form-control ex_date"></td>
        <td><input type="number" name="qty1[]" class="form-control quantity_sold" oninput="calculateAmount(this.closest('tr'))"></td>
        <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
        
    `;
}

// Search Medicine via AJAX (medicine_master से medicine_name लाना)
function searchMedicine(input) {
    const query = input.value.trim();
    const dropdown = input.nextElementSibling;

    if (query.length < 1) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
    }

    const formData = new FormData();
    formData.append('query', query);

    fetch('../purchase_medicine/search_medicine.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';

        dropdown.querySelectorAll('.product-item').forEach(item => {
            item.addEventListener('click', () => {
                const medicineName = item.textContent;
                input.value = medicineName;
                dropdown.style.display = 'none';

                const row = input.closest('tr');
                row.querySelector('.product_id').value = medicineName;
                row.querySelector('.batch').value = '';
                row.querySelector('.gst').value = '';
                row.querySelector('.mrp').value = '';
                row.querySelector('.ex_date').value = '';
                row.querySelector('.available_stock').value = '';
                row.querySelector('.quantity_sold').value = '';
                
            });
        });
    })
    .catch(error => {
        dropdown.style.display = 'none';
        console.error('Error:', error);
    });
}

// Qty × MRP = Amount + GST
function calculateAmount(row) {
    const qty = parseFloat(row.querySelector('.quantity_sold').value) || 0;
    const mrp = parseFloat(row.querySelector('.mrp').value) || 0;
    const gst = parseFloat(row.querySelector('.gst').value) || 0;

    const baseAmount = qty * mrp;
    const gstAmount = (baseAmount * gst) / 100;
    const totalAmount = baseAmount + gstAmount;

    row.querySelector('.amount').value = totalAmount.toFixed(2);

    calculateTotalAmount(); // Update overall total
}

// Total amount calculation from all rows
function calculateTotalAmount() {
    let total = 0;
    document.querySelectorAll('.amount').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('total_amount').value = total.toFixed(2);

    // Apply discount calculation
    calculateFinalTotal();
}

// Final total after discount
function calculateFinalTotal() {
    const total = parseFloat(document.getElementById('total_amount').value) || 0;
    const discount = parseFloat(document.getElementById('dics').value) || 0;
    const discountAmount = (total * discount) / 100;
    const finalTotal = total - discountAmount;

    document.getElementById('final_total_amount').value = finalTotal.toFixed(2);
}

// Discount change par auto final total calculate karo
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('dics').addEventListener('input', calculateFinalTotal);
});
