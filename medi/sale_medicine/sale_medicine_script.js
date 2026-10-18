// Medicine Row Add करने वाला Function
function addMedicineRow() {
    const table = document.getElementById("medicine_table").getElementsByTagName('tbody')[0];
    const row = table.insertRow(); // नया row जोड़ो
    const srNo = table.rows.length; // Sr No. auto generate

    // Row में input fields add करो
    row.innerHTML = `
        <td><input type="number" name="sr_no[]" class="form-control" value="${srNo}" readonly></td>
        <td>
            <input type="text" class="form-control medicine_search" placeholder="Search Medicine" onkeyup="searchMedicine(this)">
            <div class="dropdown-menu medicine_dropdown"></div>
            <input type="hidden" name="product[]" class="product_id">
        </td>
        <td><input type="text" name="batch[]" class="form-control batch" readonly></td>
        <td><input type="text" name="gst[]" class="form-control gst" readonly></td>
        <td><input type="text" name="mrp[]" class="form-control mrp" readonly></td>
        <td><input type="text" name="ex_date[]" class="form-control ex_date" readonly></td>
        <td><input type="number" name="qty1[]" class="form-control quantity_sold" oninput="calculateAmount(this.closest('tr'))"></td>
        <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
        <td><input type="text" class="form-control available_stock" readonly></td>
    `;
}

// Medicine search करने वाला function (AJAX से search_medicine.php call होता है)
function searchMedicine(input) {
    let query = input.value;
    let dropdown = input.nextElementSibling;
    
    if (query.length < 1) {
        dropdown.style.display = 'none';
        return;
    }

    // AJAX से product search
    $.ajax({
        url: 'search_medicine.php',
        method: 'POST',
        data: { query: query },
        success: function (data) {
            dropdown.innerHTML = data;
            dropdown.style.display = 'block';
        }
    });
}

// Search से item select होने पर values fill करना
function selectMedicine(option, row) {
    row.querySelector(".medicine_search").value = option.getAttribute('data-name');
    row.querySelector(".product_id").value = option.getAttribute('data-id');
    row.querySelector(".batch").value = option.getAttribute('data-batch');
    row.querySelector(".mrp").value = option.getAttribute('data-mrp');
    row.querySelector(".ex_date").value = option.getAttribute('data-expiry');
    row.querySelector(".available_stock").value = option.getAttribute('data-stock');
    row.querySelector(".gst").value = option.getAttribute('data-gst') || 0;

    row.querySelector(".medicine_dropdown").style.display = 'none';

    calculateAmount(row);
}

// GST और quantity के हिसाब से amount calculate करना
function calculateAmount(row) {
    const mrp = parseFloat(row.querySelector(".mrp").value) || 0;
    const gst = parseFloat(row.querySelector(".gst").value) || 0;
    const quantity = parseInt(row.querySelector(".quantity_sold").value) || 0;
    const availableStock = parseInt(row.querySelector(".available_stock").value) || 0;

    let finalQty = quantity;
    if (quantity > availableStock) {
        alert("Quantity exceeds available stock!");
        finalQty = availableStock;
        row.querySelector(".quantity_sold").value = availableStock;
    }

    const baseAmount = mrp * finalQty;
    const gstAmount = (baseAmount * gst) / 100;
    const totalAmount = baseAmount + gstAmount;

    row.querySelector(".amount").value = totalAmount.toFixed(2);

    updateTotalAmount();
}

// सभी rows का total amount निकालना
function updateTotalAmount() {
    let total = 0;
    document.querySelectorAll(".amount").forEach(amount => total += parseFloat(amount.value) || 0);
    document.getElementById("total_amount").value = total.toFixed(2);
    applyDiscount(); // Discount भी apply करना
}

// Discount के बाद final amount निकालना
function applyDiscount() {
    const total = parseFloat(document.getElementById("total_amount").value) || 0;
    const discount = parseFloat(document.getElementById("dics").value) || 0;
    const final = total - (total * discount / 100);
    document.getElementById("final_total_amount").value = final.toFixed(2);
}

// Page load होते ही discount field पर event लगाना
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("dics").addEventListener("input", applyDiscount);
});
