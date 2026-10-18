<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Walking Login Animation</title>
<style>
    /* Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(to right, #4facfe, #00f2fe);
        height: 100vh;
        overflow: hidden;
        position: relative;
    }

    /* Walker Animation */
    .walker {
        position: absolute;
        bottom: 200px;
        left: -200px;
        width: 300px;
        animation: walkAcross 5s linear forwards;
        z-index: 2; /* GIF ऊपर रहे */
    background: linear-gradient(45deg, #ff9a9e, #fad0c4);

        
    }
    .walker img {
    width: 100%;
    height: auto;
    background: linear-gradient(45deg, #ff9a9e, #fad0c4);
}

    @keyframes walkAcross {
        0% { left: -200px; }
        100% { left: calc(30% - 75px); }
    }

    /* Login Box */
    .login-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        width: 300px;
        opacity: 0;
        transition: opacity 1s ease;
        z-index: 1;
    }
    .login-container.show {
        opacity: 1;
    }
    .login-container h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    .login-container input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .login-container button {
        width: 100%;
        padding: 10px;
        background: #4facfe;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .login-container button:hover {
        background: #00c6ff;
    }
</style>
</head>
<body>

<!-- Walker -->
<div class="walker" id="walker">
    <img src="image/walk.gif" alt="Walking Man" style="background: linear-gradient(45deg, #ff9a9e, #fad0c4);">
</div>

<!-- Login Form -->
<div class="login-container" id="loginBox">
    <h2>Login</h2>
   <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="name" class="form-control" required placeholder="Enter your username">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Enter your password">
        </div>
        <div class="d-grid mt-3">
            <button type="submit" class="btn btn-login btn-lg">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </div>
    </form>
</div>

<script>
    // 5 सेकंड बाद वॉकर छुपाना और लॉगिन दिखाना
    setTimeout(() => {
        document.getElementById('walker').style.display = 'none';
        document.getElementById('loginBox').classList.add('show');
    }, 5000); // वॉकर एनीमेशन समय
</script>

</body>
</html>