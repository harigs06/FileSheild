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
        localStorage.setItem("name",result.first_name);
        localStorage.setItem("is_verified",result.is_verified);
        localStorage.setItem("email",result.email);
        localStorage.setItem("second_name",result.second_name);
        window.location.href = "/FileSheild/home.html?login=success";
    } else {
        alert(result.message);
    }
});