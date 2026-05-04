<?php include 'DBConn.php'; ?>

<?php
if(isset($_POST['register'])){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->query("INSERT INTO tblUser (username, email, password)
                  VALUES ('$username','$email','$password')");
    echo "Registered!";
}
?>

<form method="POST">
<input type="text" name="username" placeholder="Username" required><br>
<input type="email" name="email" placeholder="Email" required><br>
<input type="password" name="password" placeholder="Password" required><br>
<button name="register">Register</button>
</form>