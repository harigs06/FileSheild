document.querySelector("form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    const response = await fetch("/FileSheild/backend-php/login.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (result.status === "success") {
        localStorage.setItem("auth_token", result.token);
        window.location.href = "/FileSheild/home.html";
    } else {
        alert(result.message);
    }
});