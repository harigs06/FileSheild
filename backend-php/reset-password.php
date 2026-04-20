<?php

$conn = new mysqli("localhost", "root", "", "FileSheild");

$token = $_GET['token'] ?? '';

if(empty($token)){
    header("Location: /FileSheild/frontend/error.html?message=" . urlencode("Invalid Request"));
}

// check token AND reset_expiry > NOW()
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token=? ");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    header("Location: /FileSheild/frontend/error.html?message=" . urlencode("Token Expired or Invalid"));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    color: white;
}

.container {
    background: #111827;
    padding: 30px;
    border-radius: 12px;
    width: 320px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: none;
    border-radius: 6px;
    outline: none;
    font-size: 14px;
}

button {
    width: 100%;
    padding: 10px;
    background: #22c55e;
    border: none;
    border-radius: 6px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
}

button:hover {
    background: #16a34a;
}

.error {
    color: #f87171;
    font-size: 13px;
    margin-top: 5px;
}

</style>
</head>

<body>

<div class="container">
    <h2>Reset Password</h2>

    <form id="resetForm" action="update-password.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <input type="password" id="password" name="password" placeholder="New Password" required>
        <input type="password" id="confirmPassword" placeholder="Confirm Password" required>

        <div class="error" id="errorMsg"></div>

        <button type="submit">Update Password</button>
    </form>
</div>

<script>
document.getElementById("resetForm").addEventListener("submit", function(e) {
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirmPassword").value.trim();
    const errorMsg = document.getElementById("errorMsg");

    errorMsg.textContent = "";

    // Password length validation
    if (password.length < 6) {
        e.preventDefault();
        errorMsg.textContent = "Password must be at least 6 characters.";
        return;
    }

    // Confirm password match
    if (password !== confirmPassword) {
        e.preventDefault();
        errorMsg.textContent = "Passwords do not match.";
        return;
    }
});
</script>

</body>
</html>