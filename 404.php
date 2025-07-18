<?php
http_response_code(404);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 Not Found — hapka.lol</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <style>
        body {
            background: #181a1b;
            color: #f1f1f1;
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .logo {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 18px;
            background: linear-gradient(90deg, #8f5cff 0%, #5865f2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .error-block {
            background: #23272a;
            border-radius: 18px;
            box-shadow: 0 2px 16px #000a;
            padding: 38px 32px 32px 32px;
            max-width: 420px;
            text-align: center;
        }
        .error-code {
            font-size: 4.2rem;
            font-weight: 800;
            color: #8f5cff;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 24px;
        }
        .home-btn {
            display: inline-block;
            background: linear-gradient(90deg, #8f5cff 0%, #5865f2 100%);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 12px 32px;
            text-decoration: none;
            box-shadow: 0 2px 8px #0005;
            transition: background 0.2s, transform 0.1s;
        }
        .home-btn:hover {
            background: #5865f2;
            transform: translateY(-2px);
        }
        @media (max-width: 600px) {
            .error-block { padding: 18vw 2vw 10vw 2vw; max-width: 98vw; }
            .logo { font-size: 1.3rem; }
            .error-code { font-size: 2.5rem; }
        }
    </style>
</head>
<body>
    <div class="logo"><a href="/" style="text-decoration:none; color:inherit; display:inline-flex; align-items:center;"><img src="/logo.png" alt="logo" style="height:32px;vertical-align:middle;margin-right:10px;">hapka.lol</a></div>
    <div class="error-block">
        <div class="error-code">404</div>
        <div class="error-title">Page not found</div>
        <a href="/" class="home-btn">Go to main page</a>
    </div>
</body>
</html> 