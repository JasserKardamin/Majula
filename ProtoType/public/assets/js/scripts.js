let currentPage = 1;

function loadMoreProducts() {
    const loadingIndicator = document.getElementById('loading-indicator');
    loadingIndicator.style.display = 'block'; 

    currentPage++;
    fetch(`/data/${currentPage}`)
        .then(response => response.json())
        .then(data => {
            loadingIndicator.style.display = 'none'; // Hide loading indicator

            if (data.length > 0) {
                data.forEach(product => {
                    // Create a product card element
                    const productElement = document.createElement('div');
                    productElement.className = 'col mb-5';
                    productElement.innerHTML = `
                        <div class="card h-100">
                            <img class="card-img-top" src="${product.imageUrl}" alt="${product.name}" />
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <h5 class="fw-bolder">${product.name}</h5>
                                    $${product.price}
                                </div>
                            </div>
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="#">View options</a></div>
                            </div>
                        </div>
                    `;
                    document.getElementById('product-list').appendChild(productElement);
                });
            } else {
                window.removeEventListener('scroll', handleScroll); // Stop loading if no more products
            }
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            loadingIndicator.style.display = 'none'; // Hide loading indicator in case of error
        });
}

function handleScroll() {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight) {
        loadMoreProducts();
    }
}

// Attach the scroll event listener
window.addEventListener('scroll', handleScroll);
