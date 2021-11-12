<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>ChromeDriver Example</title>
	</head>
	<body>
		<form 
            method="post" 
            action="/scrape.php" 
            onsubmit="btn.disabled = true; btn.textContent = 'Processing...'; return true;"
        >
            <p>ChromeDriver Example</p>

            <button name="btn" type="submit">Run Script</button>
        </form>
	</body>
</html>