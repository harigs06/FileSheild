document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);


    if(params.get("registered") === "true"){
        showToast("Registered successfully , Please login to continue.", "success");

        window.history.replaceState({}, document.title, "/FileSheild/home.html");
        setTimeout(() => {
            window.location.href = "/FileSheild/frontend/signin.html";
        }, 2000);
    }
    if(params.get("login") === "success"){
        showToast("Logged In ", "success");

        window.history.replaceState({}, document.title, "/FileSheild/home.html");
    }
    if(params.get("logout") === "success"){
        showToast("Logged Out","error");

        window.history.replaceState({}, document.title, "/FileSheild/home.html");
    }

    const uploadBtn = document.getElementById("uploadBtn");
    const fileInput = document.getElementById("fileInput");

    if (uploadBtn) {

        uploadBtn.addEventListener("click", () => {

            const token = localStorage.getItem("auth_token");

            if (token) {

                // open file explorer
                fileInput.click();

            } else {

                showToast("Please login to upload files", "info");

                setTimeout(() => {
                    window.location.href = "/FileSheild/frontend/signin.html";
                }, 1500);

            }

        });

    }
    const token = localStorage.getItem("auth_token");
    const name = localStorage.getItem("name");


    
    
    
    
    let text = "Secure File Sharing, Simplified.";
    if(token){
    text = "Welcome, "+name;    
    }
    const typingElement = document.querySelector(".typing");

    if (typingElement) {
        let index = 0;

        function typeEffect() {
            if (index < text.length) {
                typingElement.textContent += text.charAt(index);
                index++;
                setTimeout(typeEffect, 80);
            }
        }

        typeEffect();
    }


    function showToast(message, type="info"){

        const container = document.getElementById("toast-container");
    
        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.textContent = message;
    
        container.appendChild(toast);
    
        setTimeout(()=>{
            toast.classList.add("show");
        },10);
    
        setTimeout(()=>{
            toast.classList.remove("show");
    
            setTimeout(()=>{
                toast.remove();
            },400);
    
        },4000);
    }

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
                    
                    <div class="avatar-info" id="avatarInfo">
                        <div id="profileName"></div>
                        <div id="profileEmail"></div>
                    </div>
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
            localStorage.clear();
            window.location.href = "/FileSheild/home.html?logout=success";
            

        });

        document.addEventListener("click", () => {
            dropdownMenu.classList.remove("show");
        });
    }

    if(!navRight){
        return;
    }else{
    renderNavbar();
    }


    const second_name = localStorage.getItem("second_name");
    const email = localStorage.getItem("email");

    const profileName = document.getElementById("profileName");
    const profileEmail = document.getElementById("profileEmail");

    if(profileName) profileName.textContent = name+" "+second_name;
    if(profileEmail) profileEmail.textContent = email;


    const hamburger = document.getElementById("hamburger");

    if(hamburger){
        hamburger.addEventListener("click", () => {
            navRight.classList.toggle("show");
        });
    }
});
