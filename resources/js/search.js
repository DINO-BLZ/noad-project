function escapeHtml(value) {
    const element = document.createElement("div");
    element.textContent = value ?? "";

    return element.innerHTML;
}

async function parseJsonResponse(response) {
    const contentType = response.headers.get("content-type") || "";

    if (!contentType.includes("application/json")) {
        const text = await response.text();
        throw new Error(text || "Le serveur a retourné une réponse inattendue.");
    }

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message || "Une erreur est survenue.");
    }

    return data;
}

export function initializeSearch() {
    const searchInput = document.querySelector(".site-header__search input");
    const searchResults = document.querySelector(".site-header__search-results");

    if (!searchInput || !searchResults) {
        return;
    }

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
            fetch(`/recherche/suggestions?q=${encodeURIComponent(query)}`, {
                headers: {
                    Accept: "application/json",
                },
            })
                .then(async (response) => {
                    const products = await parseJsonResponse(response);

                    if (!Array.isArray(products)) {
                        throw new Error("Format de réponse invalide.");
                    }

                    return products;
                })
                .then((products) => {
                    if (products.length === 0) {
                        searchResults.innerHTML =
                            '<p class="site-header__search-empty">Aucun résultat</p>';
                    } else {
                        searchResults.innerHTML = products.map((product) => `
                            <a href="${escapeHtml(product.url)}" class="site-header__search-item">
                                ${product.image ? `<img src="${escapeHtml(product.image)}" alt="">` : ""}
                                <span class="site-header__search-item-name">${escapeHtml(product.name)}</span>
                                <span class="site-header__search-item-price">${escapeHtml(product.price)}</span>
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
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.classList.remove("is-open");
        }
    });
}