document.addEventListener("DOMContentLoaded", () => {

    const navRight = document.getElementById("nav-right");
    const hamburger = document.getElementById("hamburger");

    /* TOAST FUNCTION */

    /* TYPING EFFECT */

    function startTyping(){

        const typingElement = document.querySelector(".typing");

        if(!typingElement) return;

        const token = localStorage.getItem("auth_token");
        const name = localStorage.getItem("name");

        let text = "Secure File Sharing, Simplified.";

        if(token && name){
            text = "Welcome, " + name;
        }

        typingElement.textContent = ""; // reset

        let index = 0;

        function type(){

            if(index < text.length){
                typingElement.textContent += text.charAt(index);
                index++;
                setTimeout(type, 60);
            }

        }

        type();
    }

    function showToast(message, type="info"){

        const container = document.getElementById("toast-container");

        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(()=>toast.classList.add("show"),10);

        setTimeout(()=>{
            toast.classList.remove("show");
            setTimeout(()=>toast.remove(),400);
        },3000);
    }

    /* URL PARAM TOASTS */

    const params = new URLSearchParams(window.location.search);

    if(params.get("registered") === "true"){
        showToast("Registered successfully!", "success");
    }

    if(params.get("login") === "success"){
        showToast("Logged In", "success");
    }

    if(params.get("logout") === "success"){
        showToast("Logged Out", "error");
    }

    /* NAVBAR RENDER */

    function renderNavbar(){

        const token = localStorage.getItem("auth_token");

        if(token){

            navRight.innerHTML = `
                <a href="#">Upload</a>
                <a href="#features">About</a>
                <a href="#contact">Contact</a>

                <div class="account-wrapper">
                    <img src="/FileSheild/assets/imgs/person.png" 
                         class="account" 
                         id="accountBtn">

                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="/FileSheild/frontend/your_uploads.html">Your Uploads</a>
                        <a href="/FileSheild/frontend/profile.html">Profile</a>
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

    /* DROPDOWN */

    function setupDropdown(){

        const accountBtn = document.getElementById("accountBtn");
        const dropdownMenu = document.getElementById("dropdownMenu");
        const logoutBtn = document.getElementById("logoutBtn");

        if(!accountBtn) return;

        accountBtn.addEventListener("click", (e)=>{
            e.stopPropagation();
            dropdownMenu.classList.toggle("show");
        });

        logoutBtn.addEventListener("click", (e)=>{
            e.preventDefault();
            localStorage.clear();
            window.location.href = "/FileSheild/home.html?logout=success";
        });

        document.addEventListener("click",(e)=>{
            if(!e.target.closest(".account-wrapper")){
                dropdownMenu.classList.remove("show");
            }
        });
    }

    /* HAMBURGER MENU */

    if(hamburger){
        hamburger.addEventListener("click", ()=>{
            navRight.classList.toggle("show");
        });
    }

    /* UPLOAD BUTTON */

    const uploadBtn = document.getElementById("uploadBtn");

    if(uploadBtn){
        uploadBtn.addEventListener("click", ()=>{

            const token = localStorage.getItem("auth_token");

            if(token){
                window.location.href='frontend/upload.html';
            }else{
                showToast("Please login first","info");
                setTimeout(()=>{
                    window.location.href="/FileSheild/frontend/signin.html";
                },1500);
            }
        });
    }

    renderNavbar();
    startTyping();

});