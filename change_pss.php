<?php
require_once "config.php";

/* ==========================
   FETCH ADMIN USER
========================== */

$id = 1; // change if needed

$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Admin user not found");
}

/* ==========================
   UPDATE LOGIC
========================== */

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username  = trim($_POST["username"]);
    $full_name = trim($_POST["full_name"]);
    $password  = $_POST["password"];
    $is_active = $_POST["is_active"];

    if (empty($username)) {
        $error = "Username cannot be empty";
    } else {

        if (!empty($password)) {
            // update with password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET username = ?, full_name = ?, password_hash = ?, is_active = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $username,
                $full_name,
                $hash,
                $is_active,
                $id
            ]);

        } else {
            // update without password
            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET username = ?, full_name = ?, is_active = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $username,
                $full_name,
                $is_active,
                $id
            ]);
        }

        $success = "Profile updated successfully";

        // reload fresh data
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #0f172a;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.card {
    width: 380px;
    background: #1e293b;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(0,0,0,.6);
}

h2 {
    text-align: center;
    margin-bottom: 25px;
}

label {
    display: block;
    margin-top: 15px;
    font-size: 14px;
    color: #cbd5f5;
}

input, select {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 6px;
    color: white;
}

button {
    margin-top: 25px;
    width: 100%;
    padding: 12px;
    background: #2563eb;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 15px;
    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.success {
    background: #064e3b;
    color: #34d399;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
}

.error {
    background: #7f1d1d;
    color: #fca5a5;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
}

.note {
    margin-top: 10px;
    font-size: 12px;
    color: #94a3b8;
    text-align: center;
}
</style>
</head>

<body>

<div class="card">

    <h2>Admin Profile</h2>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Username</label>
        <input type="text" name="username"
               value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Full Name</label>
        <input type="text" name="full_name"
               value="<?= htmlspecialchars($user['full_name']) ?>">

        <label>New Password</label>
        <input type="password" name="password"
               placeholder="Leave empty to keep old password">

        <label>Status</label>
        <select name="is_active">
            <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>>Disabled</option>
        </select>

        <button type="submit">Update Profile</button>

    </form>

    <div class="note">
        Leave password blank if you don’t want to change it
    </div>

</div>

</body>
</html>
