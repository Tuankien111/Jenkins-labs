<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Mini App API</title>
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

    <header class="bg-blue-600 text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-xl font-bold">🛒 Mini App Testing Interface</h1>
        <button onclick="loadOrders()" class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded text-sm">
            Refresh Orders
        </button>
    </header>

    <div class="flex-1 flex overflow-hidden">
        
        <div class="w-2/3 p-6 overflow-y-auto border-r border-gray-300 bg-white">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">Available Products</h2>
            <div id="product-list" class="grid grid-cols-3 gap-4">
                <p class="text-gray-500 col-span-3 text-center">Loading products...</p>
            </div>
            <div class="mt-4 text-center">
                <button id="load-more-btn" class="hidden text-blue-600 hover:underline">Load More</button>
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
        <div id="order-logs" class="space-y-1 font-mono text-xs">
            </div>
    </div>

    <script>
        // STATE
        let products = [];
        let cart = {}; // format: { productId: { ...product, qty: 1 } }

        // INIT
        document.addEventListener('DOMContentLoaded', () => {
            fetchProducts();
            loadOrders();
        });

        // --- API FUNCTIONS ---

        async function fetchProducts() {
            try {
                const res = await fetch('/api/products');
                const json = await res.json();
                // Laravel Paginate returns data inside .data
                products = json.data || []; 
                renderProducts();
            } catch (err) {
                console.error(err);
                alert('Error loading products');
            }
        }

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
                    loadOrders(); // Refresh logs
                } else {
                    alert('Error: ' + (result.message || JSON.stringify(result)));
                }
            } catch (err) {
                alert('System Error');
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.innerText = 'CREATE ORDER';
            }
        }

        async function loadOrders() {
            const container = document.getElementById('order-logs');
            container.innerHTML = '<p class="text-gray-500">Fetching...</p>';
            try {
                const res = await fetch('/api/orders');
                const json = await res.json();
                const orders = json.data || []; // Assuming paginate or resource collection
                
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
                container.innerHTML = '<p class="text-red-500">Failed to load orders.</p>';
            }
        }

        // --- UI RENDER FUNCTIONS ---

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
                        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded">$${p.price}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2 line-clamp-2">${p.description || 'No description'}</p>
                    <button class="w-full mt-2 bg-gray-100 hover:bg-blue-50 text-blue-600 text-xs font-bold py-1 rounded border border-blue-200">
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