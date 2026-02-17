// Product Data
const products = [
    { name: "Linen Summer Dress", cat: "Women", price: "$120.00", stock: 24, img: "assets/img/summer.png" },
    { name: "Executive Suite Blazer", cat: "Men", price: "$250.00", stock: 12, img: "assets/img/men.png" },
    { name: "Arctic Parka", cat: "Winter", price: "$400.00", stock: 8, img: "assets/img/winter.png" }
];

function showSection(sectionId) {
    document.getElementById('dashboardSection').style.display = sectionId === 'dashboard' ? 'block' : 'none';
    document.getElementById('productsSection').style.display = sectionId === 'products' ? 'block' : 'none';
    document.getElementById('customersSection').style.display = sectionId === 'customers' ? 'block' : 'none';
    document.getElementById('ordersSection').style.display = sectionId === 'orders' ? 'block' : 'none';
    document.getElementById('addProductSection').style.display = sectionId === 'add-product' ? 'block' : 'none';

    document.querySelectorAll('.nav-link-admin').forEach(l => l.classList.remove('active'));
    if (event) {
        event.currentTarget.classList.add('active');
    }
}

const customers = [
    { id: "CUST-101", name: "Alex Thompson", email: "alex@example.com", orders: 5, status: "active" },
    { id: "CUST-102", name: "Maria Garcia", email: "maria@example.com", orders: 2, status: "active" },
    { id: "CUST-103", name: "David Smith", email: "david@example.com", orders: 8, status: "pro" },
    { id: "CUST-104", name: "Emma Watson", email: "emma@example.com", orders: 12, status: "pro" },
    { id: "CUST-105", name: "John Doe", email: "john@example.com", orders: 1, status: "new" }
];

const renderCustomers = () => {
    const list = document.getElementById('adminCustomerList');
    if (!list) return;

    list.innerHTML = customers.map(c => `
        <tr>
            <td class="small fw-bold">${c.id}</td>
            <td>
                <div class="fw-bold">${c.name}</div>
                <div class="small text-muted">${c.email}</div>
            </td>
            <td>${c.email}</td>
            <td class="text-center">${c.orders}</td>
            <td><span class="badge bg-opacity-10 rounded-pill px-3 ${c.status === 'pro' ? 'bg-primary text-primary' : (c.status === 'active' ? 'bg-success text-success' : 'bg-info text-info')}">${c.status.toUpperCase()}</span></td>
            <td>
                <button class="btn btn-sm btn-light rounded-circle"><i class="bi bi-envelope"></i></button>
                <button class="btn btn-sm btn-light rounded-circle ms-1"><i class="bi bi-pencil"></i></button>
            </td>
        </tr>
    `).join('');
};

const orders = [
    { id: "#ORD-2026", customer: "Alex Thompson", date: "Jan 27, 2026", total: "$120.00", status: "completed", email: "alex@example.com", items: "2 Items (Summer Dress, Blazer)" },
    { id: "#ORD-2027", customer: "Maria Garcia", date: "Jan 26, 2026", total: "$85.00", status: "pending", email: "maria@example.com", items: "1 Item (Accessories)" },
    { id: "#ORD-2028", customer: "David Smith", date: "Jan 26, 2026", total: "$210.00", status: "shipped", email: "david@example.com", items: "3 Items (Men's Suit, Tie)" },
    { id: "#ORD-2029", customer: "Emma Watson", date: "Jan 25, 2026", total: "$320.00", status: "completed", email: "emma@example.com", items: "2 Items (Winter Coat, Boots)" },
    { id: "#ORD-2030", customer: "John Doe", date: "Jan 25, 2026", total: "$150.00", status: "pending", email: "john@example.com", items: "1 Item (Premium Denim)" }
];

const renderDetailedOrders = () => {
    const list = document.getElementById('adminDetailedOrderList');
    if (!list) return;

    list.innerHTML = orders.map((o, index) => `
        <tr>
            <td>
                <div class="fw-bold">${o.id}</div>
                <div class="small text-muted">${o.date}</div>
            </td>
            <td>
                <div class="fw-bold">${o.customer}</div>
                <div class="small text-muted">${o.email}</div>
            </td>
            <td>
                <div class="small">${o.items}</div>
            </td>
            <td class="fw-bold">${o.total}</td>
            <td><span class="status-pill bg-${o.status}">${o.status.toUpperCase()}</span></td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border-0 dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                    <ul class="dropdown-menu border-0 shadow-sm p-2">
                        <li><a class="dropdown-item rounded-8 mb-1" href="#" onclick="updateOrderStatus(${index}, 'shipped')">Dispatch</a></li>
                        <li><a class="dropdown-item rounded-8 mb-1" href="#" onclick="updateOrderStatus(${index}, 'completed')">Finish</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-8 text-danger" href="#">Delete</a></li>
                    </ul>
                </div>
            </td>
        </tr>
    `).join('');
};

const renderOrders = (filter = 'all') => {
    const orderList = document.getElementById('adminOrderList');
    if (!orderList) return;

    const filteredOrders = filter === 'all' ? orders : orders.filter(o => o.status === filter);

    orderList.innerHTML = filteredOrders.map((o, index) => `
        <tr>
            <td class="small fw-bold">${o.id}</td>
            <td>
                <div class="fw-bold">${o.customer}</div>
                <div class="small text-muted">${o.email}</div>
                <div class="small text-muted">${o.date}</div>
            </td>
            <td><span class="status-pill bg-${o.status}">${o.status.charAt(0).toUpperCase() + o.status.slice(1)}</span></td>
            <td class="fw-bold">${o.total}</td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                    <ul class="dropdown-menu border-0 shadow-sm p-2">
                        <li><a class="dropdown-item rounded-8 mb-1" href="#" onclick="updateOrderStatus(${index}, 'shipped')">Mark as Shipped</a></li>
                        <li><a class="dropdown-item rounded-8 mb-1" href="#" onclick="updateOrderStatus(${index}, 'completed')">Mark as Completed</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-8 text-danger" href="#">Cancel Order</a></li>
                    </ul>
                </div>
            </td>
        </tr>
    `).join('');
};

window.updateOrderStatus = (index, newStatus) => {
    orders[index].status = newStatus;
    renderOrders();
    renderDetailedOrders();
    alert(`Order ${orders[index].id} status updated to ${newStatus}`);
};

const filterOrders = (status) => {
    renderOrders(status);
};

const initSalesChart = () => {
    const chart = document.getElementById('salesChart');
    if (!chart) return;

    const salesData = [40, 65, 50, 85, 70, 95, 80];
    chart.innerHTML = salesData.map(val => `
        <div class="chart-bar-wrapper d-flex flex-column align-items-center" style="height: 100%; flex: 1;">
            <div class="chart-bar" style="height: ${val}%; width: 70%;"></div>
        </div>
    `).join('');
};

const renderProducts = () => {
    const adminProductList = document.getElementById('adminProductList');
    if (!adminProductList) return;

    adminProductList.innerHTML = products.map((p, index) => `
        <tr>
            <td><img src="${p.img}" class="product-thumb"></td>
            <td class="fw-bold">${p.name}</td>
            <td>${p.cat}</td>
            <td>$${parseFloat(p.price.replace('$', '')).toFixed(2)}</td>
            <td><span class="text-${p.stock < 10 ? 'danger' : 'success'}">${p.stock} units</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary border-0"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteProduct(${index})"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `).join('');
};

window.deleteProduct = (index) => {
    if (confirm('Are you sure you want to delete this product?')) {
        products.splice(index, 1);
        renderProducts();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    renderOrders();
    renderDetailedOrders();
    renderCustomers();
    renderProducts();
    initSalesChart();

    // Add Product Logic
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const newProduct = {
                name: document.getElementById('pName').value,
                cat: document.getElementById('pCat').value,
                price: `$${parseFloat(document.getElementById('pPrice').value).toFixed(2)}`,
                stock: parseInt(document.getElementById('pStock').value),
                img: document.getElementById('pImg').value || "assets/img/summer.png" // Default image
            };

            products.push(newProduct);
            renderProducts();

            // Close modal using Bootstrap API
            const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
            modal.hide();

            // Reset form
            addProductForm.reset();
            alert('Product added correctly to Nova Street Inventory!');
        });
    }

    // Section Add Product Logic
    const sectionAddForm = document.getElementById('sectionAddProductForm');
    if (sectionAddForm) {
        sectionAddForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const inputs = this.querySelectorAll('input');
            const cat = this.querySelector('select').value;

            const newProduct = {
                name: inputs[0].value,
                price: `$${parseFloat(inputs[1].value).toFixed(2)}`,
                stock: parseInt(inputs[2].value),
                cat: cat,
                img: inputs[3].value || "assets/img/summer.png"
            };

            products.push(newProduct);
            renderProducts();
            this.reset();
            alert('New Product Published Successfully!');
            showSection('products'); // Take back to inventory after adding
        });
    }

    // Quick Add Logic (Dashboard)
    const quickAddForm = document.getElementById('quickAddProduct');
    if (quickAddForm) {
        quickAddForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const inputs = this.querySelectorAll('input');
            const cat = this.querySelector('select').value;

            const newProduct = {
                name: inputs[0].value,
                price: `$${parseFloat(inputs[1].value).toFixed(2)}`,
                stock: parseInt(inputs[2].value),
                cat: cat,
                img: "assets/img/summer.png"
            };

            products.push(newProduct);
            renderProducts();
            this.reset();
            alert('Quick Product Added!');
        });
    }
});
