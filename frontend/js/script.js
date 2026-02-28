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