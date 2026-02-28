// Just for sample ..
const text = "Secure File Sharing, Simplified.";
const typingElement = document.querySelector(".typing");

let index = 0;

function typeEffect() {
    if (index < text.length) {
        typingElement.textContent += text.charAt(index);
        index++;
        setTimeout(typeEffect, 120);
    }
}

typeEffect();


const navRight = document.getElementById("nav-right");

function renderNavbar() {
    const isLoggedIn = localStorage.getItem("isLoggedIn");

    if (isLoggedIn === "true") {
        navRight.innerHTML = `
            <a href="#">Upload</a>
            <a href="#features">About</a>
            <a href="#contact">Contact</a>
            <img src="/assets/imgs/person.png" class="account" id="logoutBtn">
        `;

        document.getElementById("logoutBtn").addEventListener("click", () => {
            localStorage.removeItem("isLoggedIn");
            renderNavbar();
        });

    } else {
        navRight.innerHTML = `
            <a href="#">Upload</a>
            <a href="#features">About</a>
            <a href="#contact">Contact</a>
            <a href="#" id="loginBtn">Sign In</a>
            <a href="#">Sign Up</a>
        `;

        document.getElementById("loginBtn").addEventListener("click", () => {
            localStorage.setItem("isLoggedIn", "true");
            renderNavbar();
        });
    }
}

renderNavbar();