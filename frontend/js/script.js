document.addEventListener("DOMContentLoaded", () => {

    const navRight = document.getElementById("nav-right");

    function renderNavbar() {

        const token = localStorage.getItem("auth_token");

        if (token) {

            navRight.innerHTML = `
                <a href="#">Upload</a>
                <a href="#features">About</a>
                <a href="#contact">Contact</a>

                <div class="account-wrapper">
                    <img src="/FileSheild/assets/imgs/person.png" 
                         class="account" 
                         id="accountBtn">

                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="/FileSheild/frontend/uploads.html">Your Uploads</a>
                        <a href="/FileSheild/frontend/profile.html">Profile</a>
                        <a href="/FileSheild/frontend/settings.html">Settings</a>
                        <a href="#" id="logoutBtn">Logout</a>
                    </div>
                </div>
            `;

            setupDropdown();

        } else {

            navRight.innerHTML = `
                <a href="#">Upload</a>
                <a href="#features">About</a>
                <a href="#contact">Contact</a>
                <a href="/FileSheild/frontend/signin.html">Sign In</a>
                <a href="/FileSheild/frontend/signup.html">Sign Up</a>
            `;
        }
    }

    function setupDropdown() {

        const accountBtn = document.getElementById("accountBtn");
        const dropdownMenu = document.getElementById("dropdownMenu");
        const logoutBtn = document.getElementById("logoutBtn");

        accountBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle("show");
        });

        logoutBtn.addEventListener("click", (e) => {
            e.preventDefault();
            localStorage.removeItem("auth_token");
            window.location.href = "/FileSheild/home.html";
        });

        document.addEventListener("click", () => {
            dropdownMenu.classList.remove("show");
        });
    }

    renderNavbar();

});