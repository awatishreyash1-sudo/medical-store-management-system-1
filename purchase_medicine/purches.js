// 🎯 Bootstrap Alert Show Function
function showAlert(message, type = "danger") {

    const alertBox = `
        <div class="alert alert-${type} alert-dismissible fade show text-center" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    document.getElementById("jsMessage").innerHTML = alertBox;

    // 3 सेकंदात auto hide
    setTimeout(() => {
        document.getElementById("jsMessage").innerHTML = "";
    }, 3000);
}



// Medicine Row Add Function
function addMedicineRow() {
    const table = document.getElementById("medicine_table").getElementsByTagName('tbody')[0];
    const row = table.insertRow();
    const srNo = table.rows.length;

    const today = new Date().toISOString().split('T')[0];

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
        
        <td>
            <input type="date" name="ex_date[]" 
                   class="form-control ex_date" 
                   min="${today}"
                   onchange="validateExpiryDate(this)">
        </td>

        <td><input type="number" name="qty1[]" class="form-control quantity_sold" oninput="calculateAmount(this.closest('tr'))"></td>
        <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
    `;
}



// ✅ Expiry Date Validation (Bootstrap Alert)
function validateExpiryDate(input) {
    const selectedDate = input.value;
    const today = new Date().toISOString().split('T')[0];

    if (selectedDate < today) {
        showAlert("<strong>Warning!</strong> Expiry date आजच्या आधीची चालणार नाही!", "warning");
        input.value = "";
        input.focus();
    }
}



// Search Medicine
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
                row.querySelector('.quantity_sold').value = '';
            });
        });
    })
    .catch(error => {
        dropdown.style.display = 'none';
        console.error('Error:', error);
    });
}



// Amount Calculation
function calculateAmount(row) {
    const qty = parseFloat(row.querySelector('.quantity_sold').value) || 0;
    const mrp = parseFloat(row.querySelector('.mrp').value) || 0;
    const gst = parseFloat(row.querySelector('.gst').value) || 0;

    const baseAmount = qty * mrp;
    const gstAmount = (baseAmount * gst) / 100;
    const totalAmount = baseAmount + gstAmount;

    row.querySelector('.amount').value = totalAmount.toFixed(2);

    calculateTotalAmount();
}



function calculateTotalAmount() {
    let total = 0;
    document.querySelectorAll('.amount').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('total_amount').value = total.toFixed(2);

    calculateFinalTotal();
}



function calculateFinalTotal() {
    const total = parseFloat(document.getElementById('total_amount').value) || 0;
    const discount = parseFloat(document.getElementById('dics').value) || 0;
    const discountAmount = (total * discount) / 100;
    const finalTotal = total - discountAmount;

    document.getElementById('final_total_amount').value = finalTotal.toFixed(2);
}



// Form Submit Validation
document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('dics').addEventListener('input', calculateFinalTotal);

    document.querySelector("form").addEventListener("submit", function(e) {

        const today = new Date().toISOString().split('T')[0];
        let valid = true;

        document.querySelectorAll('.ex_date').forEach(input => {
            if (input.value && input.value < today) {
                showAlert("<strong>Error!</strong> Expiry date आजच्या आधीची चालणार नाही!", "danger");
                input.focus();
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
        }
    });

});