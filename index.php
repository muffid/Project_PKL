<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Nota Penjualan</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <div class="max-w-[1000px] mx-auto">
        <h2 class="text-2xl font-bold mb-4">Nota Penjualan</h2>
        <table class="w-full border-collapse rounded-lg border border-gray-400" id="itemTable">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border px-4 py-2">Item</th>
                    <th class="border px-4 py-2">Harga</th>
                    <th class="border px-4 py-2">Quantity</th>
                    <th class="border px-4 py-2">Total</th>
                    <th class="border px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <button onclick="addRow()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Tambah Item</button>

        <div class="flex justify-end mt-4">
            <label class="mr-2 font-bold">Total Sebelum Diskon:</label>
            <span id="totalBeforeDiscount" class="font-bold text-lg">0</span>
        </div>

        <div class="flex justify-end mt-2">
            <label for="discount" class="mr-2">Diskon:</label>
            <input type="number" id="discount" class="border px-2 py-1 w-24 text-right" value="0">
        </div>

        <div class="flex justify-end mt-2">
            <label class="mr-2 font-bold">TOTAL Setelah Diskon:</label>
            <span id="finalTotal" class="font-bold text-lg">0</span>
        </div>

        <button onclick="generatePDF()" class="mt-4 bg-green-500 text-white px-4 py-2 rounded">Cetak Nota</button>
    </div>
    <script>
        let itemsData = [];

        fetch('get_items_dummy.php')
            .then(response => response.json())
            .then(data => {
                itemsData = data;
            });

        function addRow() {
            let table = document.getElementById("itemTable").getElementsByTagName('tbody')[0];
            let row = table.insertRow();

            let itemCell = row.insertCell(0);
            let priceCell = row.insertCell(1);
            let quantityCell = row.insertCell(2);
            let totalCell = row.insertCell(3);
            let actionCell = row.insertCell(4);

            let select = document.createElement("select");
            select.classList.add("border", "px-2", "py-1", "item");
            select.innerHTML = `<option value="">Pilih Item</option>`;
            itemsData.forEach(item => {
                select.innerHTML += `<option value="${item.Harga_Item}">${item.Nama_Item}</option>`;
            });
            itemCell.appendChild(select);

            let priceInput = document.createElement("input");
            priceInput.type = "text";
            priceInput.classList.add("border", "px-2", "py-1", "w-24", "text-right", "price");
            priceInput.readOnly = true;
            priceCell.appendChild(priceInput);

            let quantityInput = document.createElement("input");
            quantityInput.type = "number";
            quantityInput.classList.add("border", "px-2", "py-1", "w-16", "text-right", "quantity");
            quantityInput.value = 1;
            quantityCell.appendChild(quantityInput);

            let totalSpan = document.createElement("span");
            totalSpan.classList.add("totalPrice");
            totalCell.appendChild(totalSpan);

            let deleteBtn = document.createElement("button");
            deleteBtn.innerText = "Hapus";
            deleteBtn.classList.add("bg-red-500", "text-white", "px-2", "py-1", "rounded");
            deleteBtn.onclick = () => {
                row.remove();
                updateTotal();
            };
            actionCell.appendChild(deleteBtn);

            select.addEventListener('change', function() {
                priceInput.value = this.value;
                updateTotal();
            });

            quantityInput.addEventListener('input', updateTotal);
            document.getElementById('discount').addEventListener('input', updateFinalTotal);
        }

        function updateTotal() {
            let totalBeforeDiscount = 0;
            document.querySelectorAll('tbody tr').forEach(row => {
                let price = parseFloat(row.querySelector('.price').value) || 0;
                let quantity = parseFloat(row.querySelector('.quantity').value) || 0;
                let total = price * quantity;
                row.querySelector('.totalPrice').textContent = total.toLocaleString('id-ID');
                totalBeforeDiscount += total;
            });
            document.getElementById('totalBeforeDiscount').textContent = totalBeforeDiscount.toLocaleString('id-ID');
            updateFinalTotal();
        }

        function updateFinalTotal() {
            let totalBeforeDiscount = parseFloat(document.getElementById('totalBeforeDiscount').textContent.replace(/\./g, '')) || 0;
            let discount = parseFloat(document.getElementById("discount").value) || 0;
            let finalTotal = totalBeforeDiscount - discount;
            document.getElementById('finalTotal').textContent = finalTotal.toLocaleString('id-ID');
        }

        function generatePDF() {
            let { jsPDF } = window.jspdf;
            let doc = new jsPDF({ format: 'a6' });

            doc.setFontSize(14);
            doc.text("Nota Penjualan", 40, 10);

            let y = 20;
            doc.setFontSize(10);
            doc.text("Item", 10, y);
            doc.text("Harga", 50, y);
            doc.text("Qty", 80, y);
            doc.text("Total", 110, y);
            y += 6;

            let totalBeforeDiscount = 0;
            document.querySelectorAll("tbody tr").forEach(row => {
                let itemName = row.querySelector(".item").selectedOptions[0].text;
                let price = row.querySelector(".price").value;
                let quantity = row.querySelector(".quantity").value;
                let total = parseFloat(price) * parseFloat(quantity);

                if (itemName && price) {
                    doc.text(itemName, 10, y);
                    doc.text(price, 50, y);
                    doc.text(quantity, 80, y);
                    doc.text(total.toString(), 110, y);
                    y += 6;
                    totalBeforeDiscount += total;
                }
            });

            let discount = parseFloat(document.getElementById("discount").value) || 0;
            let finalTotal = totalBeforeDiscount - discount;

            y += 6;
            doc.text("Total Sebelum Diskon: " + totalBeforeDiscount.toLocaleString('id-ID'), 10, y);
            y += 6;
            doc.text("Diskon: " + discount.toLocaleString('id-ID'), 10, y);
            y += 6;
            doc.text("Total Setelah Diskon: " + finalTotal.toLocaleString('id-ID'), 10, y);
            y += 10;
            doc.text("Nama Perusahaan", 10, y);

            window.open(doc.output('bloburl'), '_blank');
        }
    </script>

</body>
</html>
