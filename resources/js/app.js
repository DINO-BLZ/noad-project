document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {
        const div = document.createElement("div");
        div.textContent = value ?? "";
        return div.innerHTML;
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || "";
    }

    async function parseJsonResponse(response) {
        const contentType = response.headers.get("content-type") || "";

        if (!contentType.includes("application/json")) {
            const text = await response.text();

            throw new Error(
                text || "Le serveur a retourné une réponse inattendue."
            );
        }

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message || "Une erreur est survenue."
            );
        }

        return data;
    }


    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */

    const html = document.documentElement;
    const savedTheme = localStorage.getItem("noad-theme");

    if (savedTheme) {
        html.setAttribute("data-theme", savedTheme);
    }

    const themeToggle = document.getElementById("theme-toggle");

    if (themeToggle) {
        themeToggle.addEventListener("click", () => {

            const current =
                html.getAttribute("data-theme") === "light"
                    ? "light"
                    : "dark";

            const next =
                current === "light"
                    ? "dark"
                    : "light";

            html.setAttribute("data-theme", next);

            localStorage.setItem("noad-theme", next);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Recherche en direct
    |--------------------------------------------------------------------------
    */

    const searchInput =
        document.querySelector(".site-header__search input");

    const searchResults =
        document.querySelector(".site-header__search-results");

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

                fetch(
                    `/recherche/suggestions?q=${encodeURIComponent(query)}`,
                    {
                        headers: {
                            "Accept": "application/json"
                        }
                    }
                )
                    .then(async (response) => {

                        const products = await parseJsonResponse(response);

                        if (!Array.isArray(products)) {
                            throw new Error(
                                "Format de réponse invalide."
                            );
                        }

                        return products;
                    })
                    .then((products) => {

                        if (products.length === 0) {

                            searchResults.innerHTML =
                                '<p class="site-header__search-empty">Aucun résultat</p>';

                        } else {

                            searchResults.innerHTML =
                                products.map((product) => `
                                    <a
                                        href="${escapeHtml(product.url)}"
                                        class="site-header__search-item"
                                    >
                                        ${
                                            product.image
                                                ? `<img src="${escapeHtml(product.image)}" alt="">`
                                                : ""
                                        }

                                        <span class="site-header__search-item-name">
                                            ${escapeHtml(product.name)}
                                        </span>

                                        <span class="site-header__search-item-price">
                                            ${escapeHtml(product.price)}
                                        </span>
                                    </a>
                                `).join("");
                        }

                        searchResults.classList.add("is-open");
                    })
                    .catch((error) => {
                        console.error("Recherche AJAX :", error);
                    });

            }, 300);
        });

        document.addEventListener("click", (event) => {

            if (
                !searchInput.contains(event.target) &&
                !searchResults.contains(event.target)
            ) {
                searchResults.classList.remove("is-open");
            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | Cart badge
    |--------------------------------------------------------------------------
    */

    const cartBadge = document.getElementById("cart-badge");

    function updateCartBadge(count) {

        if (!cartBadge) {
            return;
        }

        const numericCount = Number(count) || 0;

        cartBadge.textContent = numericCount;

        cartBadge.style.display =
            numericCount > 0
                ? "flex"
                : "none";
    }


    /*
    |--------------------------------------------------------------------------
    | PANIER
    |--------------------------------------------------------------------------
    |
    | Compatible avec cart/index.blade.php
    |
    | .cart__item
    | .cart__qty-input
    | .cart__subtotal
    | .cart__remove
    | .cart__total-amount
    |
    */

    const cartItems =
        document.querySelectorAll(".cart__item");

    cartItems.forEach((row) => {

        const variantId =
            row.dataset.variantId;

        const quantityInput =
            row.querySelector(".cart__qty-input");

        const subtotalElement =
            row.querySelector(".cart__subtotal");

        const removeButton =
            row.querySelector(".cart__remove");


        /*
        |--------------------------------------------------------------------------
        | Modification quantité
        |--------------------------------------------------------------------------
        */

        if (quantityInput && variantId) {

            let previousQuantity =
                parseInt(quantityInput.value, 10) || 1;

            quantityInput.addEventListener("change", async () => {

                let quantity =
                    parseInt(quantityInput.value, 10);

                /*
                | Quantité minimale
                */

                if (!Number.isInteger(quantity) || quantity < 1) {

                    quantity = 1;

                    quantityInput.value = 1;
                }

                /*
                | Évite plusieurs requêtes simultanées
                */

                if (row.classList.contains("is-updating")) {
                    return;
                }

                /*
                | Rien n'a changé
                */

                if (quantity === previousQuantity) {
                    return;
                }

                row.classList.add("is-updating");

                quantityInput.disabled = true;

                try {

                    const response = await fetch(
                        `/panier/${variantId}`,
                        {
                            method: "PATCH",

                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": getCsrfToken()
                            },

                            body: JSON.stringify({
                                quantity: quantity
                            })
                        }
                    );

                    const data =
                        await parseJsonResponse(response);


                    /*
                    |--------------------------------------------------------------------------
                    | Mise à jour quantité
                    |--------------------------------------------------------------------------
                    */

                    quantityInput.value =
                        quantity;


                    /*
                    |--------------------------------------------------------------------------
                    | Mise à jour sous-total
                    |--------------------------------------------------------------------------
                    */

                    if (
                        subtotalElement &&
                        data.subtotal !== undefined
                    ) {
                        subtotalElement.textContent =
                            data.subtotal;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Mise à jour total général
                    |--------------------------------------------------------------------------
                    */

                    document
                        .querySelectorAll(".cart__total-amount")
                        .forEach((element) => {

                            if (data.total !== undefined) {

                                element.textContent =
                                    data.total;
                            }

                        });


                    /*
                    |--------------------------------------------------------------------------
                    | Mise à jour badge
                    |--------------------------------------------------------------------------
                    */

                    if (data.count !== undefined) {

                        updateCartBadge(data.count);
                    }


                    /*
                    | Sauvegarde de la nouvelle quantité
                    */

                    previousQuantity =
                        quantity;

                } catch (error) {

                    console.error(
                        "Erreur mise à jour panier :",
                        error
                    );

                    /*
                    | On remet l'ancienne quantité
                    */

                    quantityInput.value =
                        previousQuantity;

                    alert(
                        error.message ||
                        "Impossible de modifier la quantité."
                    );

                } finally {

                    quantityInput.disabled =
                        false;

                    row.classList.remove(
                        "is-updating"
                    );
                }

            });
        }


        /*
        |--------------------------------------------------------------------------
        | Suppression article
        |--------------------------------------------------------------------------
        */

        if (removeButton && variantId) {

            removeButton.addEventListener(
                "click",
                async () => {

                    if (
                        row.classList.contains(
                            "is-updating"
                        )
                    ) {
                        return;
                    }

                    row.classList.add(
                        "is-updating"
                    );

                    removeButton.disabled = true;

                    try {

                        const response =
                            await fetch(
                                `/panier/${variantId}`,
                                {
                                    method: "DELETE",

                                    headers: {
                                        "Accept":
                                            "application/json",

                                        "X-CSRF-TOKEN":
                                            getCsrfToken()
                                    }
                                }
                            );

                        const data =
                            await parseJsonResponse(
                                response
                            );


                        /*
                        | Supprime la ligne
                        */

                        row.remove();


                        /*
                        | Mise à jour total
                        */

                        document
                            .querySelectorAll(
                                ".cart__total-amount"
                            )
                            .forEach((element) => {

                                if (
                                    data.total !==
                                    undefined
                                ) {

                                    element.textContent =
                                        data.total;
                                }

                            });


                        /*
                        | Mise à jour badge
                        */

                        if (
                            data.count !==
                            undefined
                        ) {

                            updateCartBadge(
                                data.count
                            );
                        }


                        /*
                        | Panier vide
                        */

                        if (
                            data.empty ||
                            Number(data.count) === 0
                        ) {

                            window.location.reload();
                        }

                    } catch (error) {

                        console.error(
                            "Erreur suppression panier :",
                            error
                        );

                        alert(
                            error.message ||
                            "Impossible de retirer cet article."
                        );

                        removeButton.disabled =
                            false;

                        row.classList.remove(
                            "is-updating"
                        );
                    }

                }
            );
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Mini panier / Drawer
    |--------------------------------------------------------------------------
    */

    const cartDrawer =
        document.getElementById("cart-drawer");

    const cartDrawerItems =
        document.getElementById("cart-drawer-items");

    const cartDrawerTotal =
        document.getElementById("cart-drawer-total");

    const addToCartForm =
        document.getElementById("add-to-cart-form");


    function openCartDrawer(data) {

        if (!cartDrawer) {
            return;
        }

        if (
            !data.items ||
            data.items.length === 0
        ) {

            if (cartDrawerItems) {

                cartDrawerItems.innerHTML =
                    '<p class="cart-drawer__empty">Ton panier est vide.</p>';
            }

        } else {

            if (cartDrawerItems) {

                cartDrawerItems.innerHTML =
                    data.items.map((item) => `

                        <div class="cart-drawer__item">

                            <img
                                src="${escapeHtml(item.image || "")}"
                                alt=""
                            >

                            <div class="cart-drawer__item-info">

                                <p class="cart-drawer__item-name">
                                    ${escapeHtml(item.name)}
                                </p>

                                <p>
                                    Taille :
                                    ${escapeHtml(item.size)}
                                    ·
                                    Qté :
                                    ${escapeHtml(item.quantity)}
                                </p>

                            </div>

                            <span class="cart-drawer__item-subtotal">
                                ${escapeHtml(item.subtotal)}
                            </span>

                        </div>

                    `).join("");
            }
        }


        /*
        | Total drawer
        */

        if (
            cartDrawerTotal &&
            data.total !== undefined
        ) {

            cartDrawerTotal.textContent =
                data.total;
        }


        /*
        | Badge
        */

        if (data.count !== undefined) {

            updateCartBadge(
                data.count
            );
        }


        /*
        | Ouvre le drawer
        */

        cartDrawer.classList.add(
            "is-open"
        );
    }


    function closeCartDrawer() {

        if (!cartDrawer) {
            return;
        }

        cartDrawer.classList.remove(
            "is-open"
        );
    }


    document
        .getElementById("cart-drawer-close")
        ?.addEventListener(
            "click",
            closeCartDrawer
        );


    document
        .getElementById("cart-drawer-overlay")
        ?.addEventListener(
            "click",
            closeCartDrawer
        );


    document
        .getElementById("cart-drawer-later")
        ?.addEventListener(
            "click",
            closeCartDrawer
        );


    /*
    |--------------------------------------------------------------------------
    | Ajouter au panier
    |--------------------------------------------------------------------------
    */

    if (addToCartForm) {

        addToCartForm.addEventListener(
            "submit",
            async (event) => {

                event.preventDefault();

                const formData =
                    new FormData(addToCartForm);

                const submitButton =
                    addToCartForm.querySelector(
                        'button[type="submit"]'
                    );

                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {

                    const response =
                        await fetch(
                            addToCartForm.action,
                            {
                                method: "POST",

                                headers: {
                                    "X-CSRF-TOKEN":
                                        getCsrfToken(),

                                    "Accept":
                                        "application/json"
                                },

                                body: formData
                            }
                        );

                    const data =
                        await parseJsonResponse(
                            response
                        );


                    /*
                    | Ouvre le mini-panier
                    */

                    openCartDrawer(data);

                } catch (error) {

                    console.error(
                        "Erreur ajout panier :",
                        error
                    );

                    alert(
                        error.message ||
                        "Une erreur est survenue."
                    );

                } finally {

                    if (submitButton) {
                        submitButton.disabled =
                            false;
                    }
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Galerie produit
    |--------------------------------------------------------------------------
    */

    const thumbnails =
        document.querySelectorAll(
            ".product-thumbnail"
        );

    const mainImage =
        document.getElementById(
            "product-main-image"
        );


    if (mainImage && thumbnails.length) {

        thumbnails.forEach((thumbnail) => {

            thumbnail.addEventListener(
                "click",
                () => {

                    const fullImage =
                        thumbnail.dataset.full;

                    if (!fullImage) {
                        return;
                    }

                    mainImage.src =
                        fullImage;

                    thumbnails.forEach(
                        (item) => {
                            item.classList.remove(
                                "is-active"
                            );
                        }
                    );

                    thumbnail.classList.add(
                        "is-active"
                    );
                }
            );

        });
    }


    /*
    |--------------------------------------------------------------------------
    | Compte à rebours prochain Drop
    |--------------------------------------------------------------------------
    */

    const countdownEl =
        document.querySelector(
            ".home-drop__countdown"
        );


    if (countdownEl) {

        const target =
            new Date(
                countdownEl.dataset.target
            ).getTime();


        const updateCountdown = () => {

            const diff =
                target - Date.now();


            if (diff <= 0) {

                countdownEl.textContent =
                    "Disponible maintenant";

                return;
            }


            const days =
                Math.floor(
                    diff /
                    (1000 * 60 * 60 * 24)
                );


            const hours =
                Math.floor(
                    (diff /
                        (1000 * 60 * 60)) %
                    24
                );


            const minutes =
                Math.floor(
                    (diff /
                        (1000 * 60)) %
                    60
                );


            const seconds =
                Math.floor(
                    (diff / 1000) %
                    60
                );


            countdownEl.textContent =
                `${days}j ${hours}h ${minutes}m ${seconds}s`;
        };


        updateCountdown();

        setInterval(
            updateCountdown,
            1000
        );
    }

});