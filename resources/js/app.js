document.addEventListener("DOMContentLoaded", () => {
    const html = document.documentElement;
    const savedTheme = localStorage.getItem("noad-theme");

    if (savedTheme) {
        html.setAttribute("data-theme", savedTheme);
    }

    const toggle = document.getElementById("theme-toggle");

    if (toggle) {
        toggle.addEventListener("click", () => {
            const current = html.getAttribute("data-theme") === "light" ? "light" : "dark";
            const next = current === "light" ? "dark" : "light";

            html.setAttribute("data-theme", next);
            localStorage.setItem("noad-theme", next);
        });
    }

    // --- Recherche en direct (AJAX) ---
    const searchInput = document.querySelector(".site-header__search input");
    const searchResults = document.querySelector(".site-header__search-results");

    if (searchInput && searchResults) {
        let debounceTimer;

        searchInput.addEventListener("input", () => {
            clearTimeout(debounceTimer);
            const query = searchInput.value.trim();

            if (query === "") {
                searchResults.innerHTML = "";
                searchResults.classList.remove("is-open");
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/recherche/suggestions?q=${encodeURIComponent(query)}`)
                    .then((response) => response.json())
                    .then((products) => {
                        if (products.length === 0) {
                            searchResults.innerHTML = '<p class="site-header__search-empty">Aucun résultat</p>';
                        } else {
                            searchResults.innerHTML = products.map((product) => `
                                <a href="${product.url}" class="site-header__search-item">
                                    ${product.image ? `<img src="${product.image}" alt="">` : ""}
                                    <span class="site-header__search-item-name">${product.name}</span>
                                    <span class="site-header__search-item-price">${product.price}</span>
                                </a>
                            `).join("");
                        }
                        searchResults.classList.add("is-open");
                    });
            }, 300);
        });

        document.addEventListener("click", (event) => {
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.classList.remove("is-open");
            }
        });
    }

    // --- Panier (AJAX) ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const cartItems = document.querySelectorAll(".cart__item");

    cartItems.forEach((row) => {
        const variantId = row.dataset.variantId;
        const qtyInput = row.querySelector(".cart__qty-input");
        const subtotalEl = row.querySelector(".cart__subtotal");
        const removeBtn = row.querySelector(".cart__remove");

        qtyInput.addEventListener("change", () => {
            const quantity = parseInt(qtyInput.value, 10);

            if (quantity < 1) {
                qtyInput.value = 1;
                return;
            }

            row.classList.add("is-updating");

            fetch(`/panier/${variantId}`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ quantity }),
            })
                .then((response) => response.json())
                .then((data) => {
                    subtotalEl.textContent = data.subtotal;
                    document.querySelector(".cart__total-amount").textContent = data.total;
                    updateCartBadge(data.count);
                    row.classList.remove("is-updating");
                });
        });

        removeBtn.addEventListener("click", () => {
            fetch(`/panier/${variantId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    row.remove();
                    document.querySelector(".cart__total-amount").textContent = data.total;
                    updateCartBadge(data.count);

                    if (data.empty) {
                        document.querySelector(".cart__items").innerHTML = '<p class="cart__empty">Ton panier est vide.</p>';
                        document.querySelector(".cart__total")?.remove();
                        document.querySelector(".checkout__link")?.remove();
                    }
                });
        });
    });

    // --- Mini-panier (tiroir coulissant) ---
    const cartDrawer = document.getElementById("cart-drawer");
    const cartDrawerItems = document.getElementById("cart-drawer-items");
    const cartDrawerTotal = document.getElementById("cart-drawer-total");
    const cartBadge = document.getElementById("cart-badge");
    const addToCartForm = document.getElementById("add-to-cart-form");

    function updateCartBadge(count) {
        if (!cartBadge) return;
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? "flex" : "none";
    }

    function openCartDrawer(data) {
        if (!cartDrawer) return;

        if (data.items.length === 0) {
            cartDrawerItems.innerHTML = '<p class="cart-drawer__empty">Ton panier est vide.</p>';
        } else {
            cartDrawerItems.innerHTML = data.items.map((item) => `
                <div class="cart-drawer__item">
                    <img src="${item.image}" alt="">
                    <div class="cart-drawer__item-info">
                        <p class="cart-drawer__item-name">${item.name}</p>
                        <p>Taille : ${item.size} · Qté : ${item.quantity}</p>
                    </div>
                    <span class="cart-drawer__item-subtotal">${item.subtotal}</span>
                </div>
            `).join("");
        }

        cartDrawerTotal.textContent = data.total;
        updateCartBadge(data.count);
        cartDrawer.classList.add("is-open");
    }

    function closeCartDrawer() {
        cartDrawer?.classList.remove("is-open");
    }

    document.getElementById("cart-drawer-close")?.addEventListener("click", closeCartDrawer);
    document.getElementById("cart-drawer-overlay")?.addEventListener("click", closeCartDrawer);
    document.getElementById("cart-drawer-later")?.addEventListener("click", closeCartDrawer);

    if (addToCartForm) {
        addToCartForm.addEventListener("submit", (event) => {
            event.preventDefault();

            const formData = new FormData(addToCartForm);

            fetch(addToCartForm.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json",
                },
                body: formData,
            })
                .then((response) => response.json().then((data) => ({ status: response.status, data })))
                .then(({ status, data }) => {
                    if (status !== 200) {
                        alert(data.message || "Une erreur est survenue.");
                        return;
                    }

                    openCartDrawer(data);
                });
        });
    }
    // --- Galerie produit ---
    const thumbnails = document.querySelectorAll('.product__thumbnail');
    const mainImage = document.getElementById('product-main-image');

    thumbnails.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            mainImage.src = thumb.dataset.full;
            thumbnails.forEach((t) => t.classList.remove('is-active'));
            thumb.classList.add('is-active');
        });
    });
});