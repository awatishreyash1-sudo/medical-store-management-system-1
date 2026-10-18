
$(document).ready(function(){
    $('#supplier_name').on('input',function(){
        var query=$(this).val();
        if(query.length>=2){
            $.ajax({
                url:'supplier_search.php',
                method:'POST',
                data:{query:query},
                success:function(data){
                    $('#supplier_suggestions').fadeIn().html(data);
                }
            });
        } else {
            $('#supplier_suggestions').fadeOut().html('');
        }
    });
    $(document).on('click','.suggestion-item',function(){
        $('#supplier_name').val($(this).text());
        $('#supplier_suggestions').fadeOut();
    });
});

function printSection(id){
    const section=document.getElementById(id).cloneNode(true);
    const win=window.open('','', 'width=1000,height=800');
    win.document.write(`<html><head><title>Purchase Bill Print</title><style>
        body{font-family:Arial;margin:30px;color:#333;}
        table{width:100%;border-collapse:collapse;margin-top:20px;font-size:14px;}
        th,td{border:1px solid #ddd;padding:12px;text-align:center;}
        th{background-color:#198754;color:white;font-weight:bold;}
        .invoice-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;border-bottom:2px solid #198754;padding-bottom:15px;}
        .invoice-header h1{font-size:28px;color:#198754;margin:0;}
        .store-info{text-align:right;font-size:14px;}
        .invoice-footer{margin-top:40px;border-top:2px solid #198754;padding-top:15px;text-align:center;font-size:14px;color:#555;}
    </style></head><body>
    <div class="invoice-header"><h1>Medical Store</h1><div class="store-info"><p>Shop No.101, Main Bazar, Dudhagon-416315</p><p>GSTIN:27ABCDE1234F1Z5</p><p>Phone:9876543210</p></div></div>
    ${section.innerHTML}
    <div class="invoice-footer"><p>Thank you for your business!</p><p>Printed on: ${new Date().toLocaleString()}</p></div>
    </body></html>`);
    win.document.close(); win.focus(); win.print(); win.close();
}

function printRecords(){
    const printContent=document.getElementById("print-area").innerHTML;
    const win=window.open('','', 'width=1000,height=800');
    win.document.write(`<html><head><title>Print All Purchase Bills</title><style>
        body{font-family:Arial;margin:30px;color:#333;}
        table{width:100%;border-collapse:collapse;margin-top:20px;font-size:14px;}
        th,td{border:1px solid #ddd;padding:12px;text-align:center;}
        th{background-color:#198754;color:white;font-weight:bold;}
        .invoice-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;border-bottom:2px solid #198754;padding-bottom:15px;}
        .invoice-header h1{font-size:28px;color:#198754;margin:0;}
        .store-info{text-align:right;font-size:14px;}
        .invoice-footer{margin-top:40px;border-top:2px solid #198754;padding-top:15px;text-align:center;font-size:14px;color:#555;}
    </style></head><body>
    <div class="invoice-header"><h1>Medical Store</h1><div class="store-info"><p>Shop No.101, Main Bazar, Dudhagon-416315</p><p>GSTIN:27ABCDE1234F1Z5</p><p>Phone:9876543210</p></div></div>
    ${printContent}
    <div class="invoice-footer"><p>Thank you for your business!</p><p>Printed on: ${new Date().toLocaleString()}</p></div>
    </body></html>`);
    win.document.close(); win.focus(); win.print(); win.close();
}

