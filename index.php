<?php
// Display PHP errors
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Init session
session_start();

// Connect to DB
$db = new PDO('mysql:host=localhost;dbname=nabidky', 'nabidky', 'Nabidky123');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Login form submitted
if (isset($_POST['login'])) {
	$hash = md5($_POST['password']); //https://www.md5.cz - generated hash - heslo: mobil1
	$query = $db->prepare('SELECT id, login, password, name FROM users WHERE login = :login and password = :password');
	$query->bindParam(':login', $_POST['login']);
	$query->bindParam(':password', $hash);
	$query->execute();
	$user = $query->fetch();

	if (!$user) {
		echo '<div class="alert alert-danger">Uživatel s těmito údaji bohužel neexistuje</div>';
	} else {
		$_SESSION['user'] = $user;
	}
}

// Logout form submitted
if (isset($_POST['logout'])) {
	unset($_SESSION);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Nabídky</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css">
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="col">

				<?php if (!isset($_SESSION['user'])) { ?>

					<h1>Přihlásit</h1>
					<form method="post">
						<div class="mb-3">
							<label for="login" class="form-label">Uživatelské jméno</label>
							<input type="text" name="login" id="login" class="form-control" />
						</div>
						<div class="mb-3">
							<label for="password" class="form-label">Heslo</label>
							<input type="password" name="password" id="password" class="form-control" />
						</div>
						<button type="submit" class="btn btn-primary">Přihlásit</button>
					</form>

				<?php } else { ?>

					<form method="post">
						<p class="text-end mt-3">Přihlášen <?= $_SESSION['user']['name'] ?> <button type="submit" name="logout" class="btn btn-danger">Odhlásit</button></p>
					</form>

					<?php
					// Include page
					$page = $_GET['page'];

					if (!isset($page) || !in_array($page, array('nabidky', 'detail'))) {
						$page = 'nabidky';
					}

					include_once($page . '.php');
					?>

				<?php } ?>

			</div>
		</div>
	</div>

</body>

</html>