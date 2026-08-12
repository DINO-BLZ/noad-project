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
});