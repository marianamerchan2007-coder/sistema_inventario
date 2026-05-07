document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebar-overlay");
    const btnToggle = document.getElementById("menu-toggle");
    const btnClose = document.getElementById("btn-close-sidebar");

    const userInfo = document.querySelector(".user-info");
    const userMenu = document.getElementById("userMenu");

    /* SIDEBAR */
    btnToggle?.addEventListener("click", () => {
        sidebar.classList.add("active");
        overlay.classList.add("active");
        btnToggle.classList.add("hidden");
    });

    function closeSidebar() {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
        btnToggle.classList.remove("hidden");
    }

    btnClose?.addEventListener("click", closeSidebar);
    overlay?.addEventListener("click", closeSidebar);

    /* USER MENU */
    if (userInfo && userMenu) {

        userInfo.addEventListener("click", (e) => {
            e.stopPropagation();
            userMenu.style.display =
                (userMenu.style.display === "block") ? "none" : "block";
        });

        document.addEventListener("click", (e) => {
            if (!userInfo.contains(e.target)) {
                userMenu.style.display = "none";
            }
        });
    }

});