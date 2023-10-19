<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User</title>
</head>
<body>
    <h1>Hello, <?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : 'User'; ?></h1>
</body>
</html>