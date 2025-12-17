<?php
// Initialize variables
$name = $email = $password = $confirm_password = "";
$errors = [];
$success_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect and validate name
    $name = trim($_POST["name"]);
    if (empty($name)) {
        $errors['name'] = "Name is required";
    }

    // Collect and validate email
    $email = trim($_POST["email"]);
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    // Password validation
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($password)) {
        $errors['password'] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters long";
    } elseif (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $errors['password'] = "Password must include a special character";
    }

    // Confirm password validation
    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Confirm password is required";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // If no validation errors
    if (empty($errors)) {

        $file = "users.json";

        // Read file contents
        if (file_exists($file)) {
            $json_data = file_get_contents($file);
            if ($json_data === false) {
                $errors['file'] = "Failed to read users.json file.";
            } else {
                $users = json_decode($json_data, true);

                if (!is_array($users)) {
                    $users = [];
                }
            }
        } else {
            $errors['file'] = "users.json file not found!";
        }

        // If no file errors
        if (empty($errors)) {

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Create user array
            $new_user = [
                "name" => $name,
                "email" => $email,
                "password" => $hashed_password
            ];

            // Add user to array
            $users[] = $new_user;

            // Write updated data to file
            $updated_json = json_encode($users, JSON_PRETTY_PRINT);

            if (file_put_contents($file, $updated_json) === false) {
                $errors['file'] = "Failed to write to users.json";
            } else {
                $success_message = "Registration successful!";
                $name = $email = ""; // clear form
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        .error { color: red; }
        .success { color: green; font-weight: bold; }
        form { width: 300px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 5px; }
        button { margin-top: 15px; padding: 8px; width: 100%; }
    </style>
</head>
<body>

<h2 style="text-align:center;">User Registration</h2>

<?php if (!empty($success_message)): ?>
    <div class="success"><?= $success_message ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
    <span class="error"><?= $errors['name'] ?? '' ?></span>

    <label>Email:</label>
    <input type="text" name="email" value="<?= htmlspecialchars($email) ?>">
    <span class="error"><?= $errors['email'] ?? '' ?></span>

    <label>Password:</label>
    <input type="password" name="password">
    <span class="error"><?= $errors['password'] ?? '' ?></span>

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password">
    <span class="error"><?= $errors['confirm_password'] ?? '' ?></span>

    <button type="submit">Register</button>

    <span class="error"><?= $errors['file'] ?? '' ?></span>
</form>

</body>
</html>