<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vercel PHP Example</title>
</head>
<body>
    <h1>Welcome to Vercel with PHP</h1>
    <p>This is a static HTML page in public folder.</p>
    <button onclick="getPHPResult()">Call PHP Function</button>

    <script>
        async function getPHPResult() {
            const response = await fetch('/api/index.php');
            const data = await response.text();
            alert(data);
        }
    </script>
</body>
</html>
