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

            // On attend 300ms après la dernière frappe avant d'interroger le serveur
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

        // Fermer le menu déroulant si on clique ailleurs sur la page
        document.addEventListener("click", (event) => {
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.classList.remove("is-open");
            }
        });
    }
});