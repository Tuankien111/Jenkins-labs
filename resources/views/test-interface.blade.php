<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Mini App API (Mock Mode) Tuấn Kiện</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for aesthetics */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="bg-gray-100 font-sans h-screen flex flex-col">

    <header class="bg-indigo-600 text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-xl font-bold">🛒 Mini App Production (Mock Data) Tuấn Kiện</h1>
        <button onclick="loadOrders()" class="bg-indigo-700 hover:bg-indigo-800 px-4 py-2 rounded text-sm">
            Refresh Orders
        </button>
    </header>

    <div class="flex-1 flex overflow-hidden">
        
        <div class="w-2/3 p-6 overflow-y-auto border-r border-gray-300 bg-white">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Available Products</h2>
                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded border border-yellow-200">⚠️ Running in Mock Data Mode</span>
            </div>
            
            <div id="product-list" class="grid grid-cols-3 gap-4">
                <p class="text-gray-500 col-span-3 text-center">Loading products...</p>
            </div>
        </div>

        <div class="w-1/3 bg-gray-50 flex flex-col border-l border-gray-200">
            <div class="p-6 flex-1 overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">New Order</h2>
                
                <div id="cart-items" class="space-y-2 mb-6">
                    <p class="text-sm text-gray-400 italic text-center" id="empty-cart-msg">Cart is empty. Click products to add.</p>
                </div>

                <div class="flex justify-between items-center border-t pt-4 mb-4">
                    <span class="font-bold text-gray-700">Estimated Total:</span>
                    <span class="font-bold text-xl text-green-600" id="cart-total">$0.00</span>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                        <input type="text" id="c-name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" id="c-phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="0909xxxxxx">
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-t border-gray-200">
                <button onclick="submitOrder()" id="btn-submit" class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                    CREATE ORDER
                </button>
            </div>
        </div>
    </div>

    <div class="h-1/4 bg-gray-800 text-white p-4 overflow-y-auto">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-2">Recent Order Logs (From API)</h3>
        <div id="order-logs" class="space-y-1 font-mono text-xs"></div>
    </div>

    <script>
        // STATE
        let products = [];
        let cart = {}; 

        // --- MOCK DATA CONFIGURATION ---
        const MOCK_PRODUCTS = [
            { id: 1, name: "iPhone 15 Pro Max (Mock)", price: 1199.00, description: "Titanium design, A17 Pro chip." },
            { id: 2, name: "MacBook Air M3 (Mock)", price: 1099.00, description: "Lean. Mean. M3 machine." },
            { id: 3, name: "Sony WH-1000XM5 (Mock)", price: 348.00, description: "Industry-leading noise canceling." },
            { id: 4, name: "Samsung Galaxy S24 (Mock)", price: 799.00, description: "Galaxy AI is here." },
            { id: 5, name: "Logitech MX Master 3S (Mock)", price: 99.00, description: "Performance wireless mouse." },
            { id: 6, name: "Keychron Q1 Pro (Mock)", price: 199.00, description: "Custom mechanical keyboard." },
            { id: 7, name: "Dell XPS 15 (Mock)", price: 1499.00, description: "Immersive 4K OLED display." },
            { id: 8, name: "iPad Air 5 (Mock)", price: 599.00, description: "Supercharged by M1." },
            { id: 9, name: "AirPods Pro 2 (Mock)", price: 249.00, description: "Rebuilt from the sound up." }
        ];

        // INIT
        document.addEventListener('DOMContentLoaded', () => {
            fetchProductsMock(); // Sử dụng hàm mock thay vì fetch API thật
            loadOrders();
        });

        // --- FUNCTIONS ---

        // Thay thế hàm fetchProducts cũ bằng hàm này
        function fetchProductsMock() {
            const list = document.getElementById('product-list');
            // Giả lập loading network
            setTimeout(() => {
                products = MOCK_PRODUCTS;
                renderProducts();
            }, 500);
        }

        // Giữ nguyên logic submit để test kết nối API (Dù có thể lỗi validation DB)
        async function submitOrder() {
            const name = document.getElementById('c-name').value;
            const phone = document.getElementById('c-phone').value;
            const items = Object.values(cart).map(item => ({
                product_id: item.id,
                quantity: item.qty
            }));

            if (!name || !phone || items.length === 0) {
                alert('Please fill in name, phone and add items to cart');
                return;
            }

            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerText = 'Processing...';

            try {
                const res = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_name: name,
                        customer_phone: phone,
                        items: items
                    })
                });

                const result = await res.json();

                if (res.ok) {
                    alert('Order created successfully! ID: ' + result.data.id);
                    cart = {};
                    document.getElementById('c-name').value = '';
                    document.getElementById('c-phone').value = '';
                    renderCart();
                    loadOrders(); 
                } else {
                    // Hiển thị lỗi rõ ràng (thường là lỗi validation do product id không có trong DB)
                    console.log(result);
                    alert('Backend Error: ' + (result.message || JSON.stringify(result)));
                }
            } catch (err) {
                alert('System Error: Check console');
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.innerText = 'CREATE ORDER';
            }
        }

        async function loadOrders() {
            const container = document.getElementById('order-logs');
            container.innerHTML = '<p class="text-gray-500">Fetching logs...</p>';
            try {
                const res = await fetch('/api/orders');
                const json = await res.json();
                const orders = json.data || [];
                
                let html = '';
                orders.forEach(o => {
                    const time = new Date(o.created_at).toLocaleString();
                    html += `<div class="border-b border-gray-700 pb-1 mb-1">
                        <span class="text-green-400">[SUCCESS]</span> 
                        Order <span class="text-yellow-400">#${o.id}</span> | 
                        ${o.customer_name} | 
                        Total: $${o.total_amount} | 
                        Items: ${o.items ? o.items.length : 'N/A'} |
                        <span class="text-gray-500">${time}</span>
                    </div>`;
                });
                container.innerHTML = html || '<p class="text-gray-500">No orders found.</p>';
            } catch (err) {
                container.innerHTML = '<p class="text-red-500">Failed to load orders (Is DB migrated?).</p>';
            }
        }

        // --- UI RENDER FUNCTIONS (Giữ nguyên) ---

        function renderProducts() {
            const container = document.getElementById('product-list');
            container.innerHTML = '';
            
            if(products.length === 0) {
                container.innerHTML = '<p>No products found</p>';
                return;
            }

            products.forEach(p => {
                const card = document.createElement('div');
                card.className = 'bg-white p-4 rounded shadow hover:shadow-lg transition border border-gray-200 cursor-pointer relative group';
                card.onclick = () => addToCart(p);
                card.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-sm text-gray-800 h-10 overflow-hidden line-clamp-2">${p.name}</h3>
                        <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2 py-1 rounded">$${p.price}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2 line-clamp-2">${p.description || 'No description'}</p>
                    <button class="w-full mt-2 bg-gray-100 hover:bg-indigo-50 text-indigo-600 text-xs font-bold py-1 rounded border border-indigo-200">
                        + Add to Cart
                    </button>
                `;
                container.appendChild(card);
            });
        }

        function addToCart(product) {
            if (cart[product.id]) {
                cart[product.id].qty++;
            } else {
                cart[product.id] = { ...product, qty: 1 };
            }
            renderCart();
        }

        function removeFromCart(id) {
            delete cart[id];
            renderCart();
        }

        function updateQty(id, delta) {
            if (cart[id]) {
                cart[id].qty += delta;
                if (cart[id].qty <= 0) delete cart[id];
                renderCart();
            }
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            const emptyMsg = document.getElementById('empty-cart-msg');
            const totalEl = document.getElementById('cart-total');
            
            container.innerHTML = '';
            const items = Object.values(cart);
            
            if (items.length === 0) {
                emptyMsg.style.display = 'block';
                totalEl.innerText = '$0.00';
                return;
            }
            
            emptyMsg.style.display = 'none';
            let total = 0;

            items.forEach(item => {
                const lineTotal = item.price * item.qty;
                total += lineTotal;

                const div = document.createElement('div');
                div.className = 'flex justify-between items-center bg-white p-2 rounded shadow-sm text-sm';
                div.innerHTML = `
                    <div class="flex-1">
                        <div class="font-bold truncate w-32">${item.name}</div>
                        <div class="text-gray-500 text-xs">$${item.price} x ${item.qty}</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="updateQty(${item.id}, -1)" class="px-2 py-0 bg-gray-200 rounded hover:bg-gray-300">-</button>
                        <span class="font-mono">${item.qty}</span>
                        <button onclick="updateQty(${item.id}, 1)" class="px-2 py-0 bg-gray-200 rounded hover:bg-gray-300">+</button>
                        <button onclick="removeFromCart(${item.id})" class="text-red-500 ml-1">x</button>
                    </div>
                `;
                container.appendChild(div);
            });

            totalEl.innerText = '$' + total.toFixed(2);
        }
    </script>
</body>
</html>