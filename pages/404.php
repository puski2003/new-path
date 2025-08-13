<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>404 — Page Not Found</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
            color: #333;
        }

        h1 {
            font-size: 4rem;
            margin: 0;
        }

        p {
            color: #666;
        }

        a {
            color: #4A6CF7;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <h1>404</h1>
    <p>That page doesn't exist.</p>
    <a href="/">Go home</a>
</body>

</html>