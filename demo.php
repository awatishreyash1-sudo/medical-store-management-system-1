
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walking Login Animation</title>

    <style>
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

        .walker {
            position: absolute;
            bottom: 200px;
            left: -200px;
            width: 300px;
            animation: walkAcross 5s linear forwards;
            z-index: 2;
        }

        .walker img {
            width: 100%;
            height: auto;
        }

        @keyframes walkAcross {
            0% {
                left: -200px;
            }
            100% {
                left: calc(30% - 75px);
            }
        }

        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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

        .login-container label {
            display: block;
            margin-top: 10px;
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

    <div class="walker" id="walker">
        <img src="image/walk.gif" alt="Walking Man">
    </div>

    <div class="login-container" id="loginBox">
        <h2>Login</h2>

        <form method="POST">
            <label for="name">Username</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                placeholder="Enter your username"
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                placeholder="Enter your password"
            >

            <button type="submit">Login</button>
        </form>
    </div>

    <script>
        setTimeout(() => {
            document.getElementById('walker').style.display = 'none';
            document.getElementById('loginBox').classList.add('show');
        }, 5000);
    </script>

</body>
</html>
