<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Shop - Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <i class="fas fa-coffee text-3xl text-brown-600 mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900">Coffee Shop</h1>
                </div>
                <nav class="flex space-x-8">
                    <a href="#" class="text-gray-500 hover:text-gray-900">Home</a>
                    <a href="#" class="text-gray-900 font-medium">Products</a>
                    <a href="#" class="text-gray-500 hover:text-gray-900">About</a>
                    <a href="#" class="text-gray-500 hover:text-gray-900">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="Search coffee products..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brown-500">
                </div>
                <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brown-500">
                    <option value="">All Categories</option>
                    <!-- Categories will be loaded dynamically -->
                </select>
                <button onclick="loadProducts()" class="px-6 py-2 bg-brown-600 text-white rounded-lg hover:bg-brown-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div id="productsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Products will be loaded here -->
        </div>

        <!-- Loading State -->
        <div id="loading" class="text-center py-8 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-brown-600"></div>
            <p class="mt-2 text-gray-600">Loading products...</p>
        </div>

        <!-- No Products State -->
        <div id="noProducts" class="text-center py-8 hidden">
            <i class="fas fa-coffee text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">No products found matching your criteria.</p>
        </div>
    </main>

    <!-- Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900"></h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="modalContent">
                    <!-- Modal content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load products and categories on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
            loadProducts();
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            // Debounce search
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                loadProducts();
            }, 500);
        });

        // Category filter
        document.getElementById('categoryFilter').addEventListener('change', function() {
            loadProducts();
        });

        function loadCategories() {
            fetch('/api/products/categories')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const categoryFilter = document.getElementById('categoryFilter');
                        // Clear existing options except "All Categories"
                        categoryFilter.innerHTML = '<option value="">All Categories</option>';
                        
                        // Add dynamic categories
                        data.data.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category;
                            option.textContent = category;
                            categoryFilter.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                });
        }

        function loadProducts() {
            const searchQuery = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            
            showLoading();
            
            let url = '/api/products';
            const params = new URLSearchParams();
            
            if (searchQuery) params.append('q', searchQuery);
            if (category) params.append('category', category);
            
            if (params.toString()) {
                url = '/api/products/search?' + params.toString();
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success && data.data.length > 0) {
                        displayProducts(data.data);
                        hideNoProducts();
                    } else {
                        showNoProducts();
                        clearProducts();
                    }
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    hideLoading();
                    showNoProducts();
                });
        }

        function displayProducts(products) {
            const container = document.getElementById('productsContainer');
            container.innerHTML = '';

            products.forEach(product => {
                const productCard = createProductCard(product);
                container.appendChild(productCard);
            });
        }

        function createProductCard(product) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow';
            
            card.innerHTML = `
                <div class="aspect-w-1 aspect-h-1 bg-gray-200">
                    ${product.image ? 
                        `<img src="${product.image}" alt="${product.name}" class="w-full h-48 object-cover">` :
                        `<div class="w-full h-48 flex items-center justify-center">
                            <i class="fas fa-coffee text-4xl text-gray-400"></i>
                        </div>`
                    }
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">${product.name}</h3>
                        <span class="text-lg font-bold text-brown-600">$${parseFloat(product.price).toFixed(2)}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">${product.description}</p>
                    <div class="flex justify-between items-center">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            ${product.category}
                        </span>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 mr-2">${product.rate}</span>
                            <div class="flex text-yellow-400">
                                ${generateStars(product.rate)}
                            </div>
                        </div>
                    </div>
                    <button onclick="showProductDetails(${product.id})" 
                            class="w-full mt-3 bg-brown-600 text-white py-2 px-4 rounded-lg hover:bg-brown-700 transition-colors">
                        View Details
                    </button>
                </div>
            `;
            
            return card;
        }

        function generateStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fas fa-star"></i>';
                } else {
                    stars += '<i class="far fa-star"></i>';
                }
            }
            return stars;
        }

        function showProductDetails(productId) {
            fetch(`/api/products/${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.data;
                        document.getElementById('modalTitle').textContent = product.name;
                        document.getElementById('modalContent').innerHTML = `
                            <div class="text-center mb-4">
                                                            ${product.image ? 
                                `<img src="${product.image}" alt="${product.name}" class="w-full h-48 object-cover rounded-lg mx-auto">` :
                                `<div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center mx-auto">
                                    <i class="fas fa-coffee text-4xl text-gray-400"></i>
                                </div>`
                            }
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="font-medium">Price:</span>
                                    <span class="text-brown-600 font-bold">$${parseFloat(product.price).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Category:</span>
                                    <span class="text-blue-600">${product.category}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Rating:</span>
                                    <div class="flex items-center">
                                        <span class="mr-2">${product.rate}</span>
                                        <div class="flex text-yellow-400">
                                            ${generateStars(product.rate)}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium">Description:</span>
                                    <p class="text-gray-600 mt-1">${product.description}</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('productModal').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading product details:', error);
                });
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading').classList.add('hidden');
        }

        function showNoProducts() {
            document.getElementById('noProducts').classList.remove('hidden');
        }

        function hideNoProducts() {
            document.getElementById('noProducts').classList.add('hidden');
        }

        function clearProducts() {
            document.getElementById('productsContainer').innerHTML = '';
        }

        // Close modal when clicking outside
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html> 