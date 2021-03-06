<?php
	session_start();
	include_once 'assets/php/functions.php';
	$user = new User();

	if(isset($_SESSION['allergyhelp_id']))
		$id = $_SESSION['allergyhelp_id'];
	if (isset($_GET['q']))
	{
		$user->logout();
		header("location:index.php");
	}
	if (isset($_REQUEST['login']))
	{
		extract($_REQUEST);
		$login = $user->check_login($email, $password);
		if ($login) header("location:index.php");
		else $_SESSION['allergyhelp_login_fail'] = true;
	}
	if (isset($_REQUEST['register']))
	{
		extract($_REQUEST);
		$register = $user->register($reg_email, $reg_password, $reg_lastname, $reg_firstname);
		if ($register)
		{
			$user->check_login($reg_email, $reg_password);
			header("location:index.php");
		}
		else $_SESSION['allergyhelp_register_fail'] = true;
	}
	if (isset($_REQUEST['editp_user']))
	{
		extract($_REQUEST);
		$edit = $user->edit_profile($id, $editp_email, $editp_password, $editp_lastname, $editp_firstname);
		if ($edit) $_SESSION['allergyhelp_editp_user_success'] = true;
		else $_SESSION['allergyhelp_editp_user_fail'] = true;
	}
	if (isset($_REQUEST['new_message']))
	{
		extract($_REQUEST);
		$send = $user->send_message($id, $subject, $message);
		if ($send)
		{
			$_SESSION['allergyhelp_send_message_success'] = true;
			header("location:index.php?p=messages");
		}
		else $_SESSION['allergyhelp_send_message_fail'] = true;
	}
	if (isset($_REQUEST['new_reply']))
	{
		extract($_REQUEST);
		$conversation = $_GET['m'];
		$user->send_reply($id, $reply, $conversation);
		header("location:index.php?p=messages&m=".$conversation);
	}
	if (isset($_REQUEST['bot_reply']))
	{
		extract($_REQUEST);
		$user->send_bot_reply($id, $reply);
		header("location:index.php?p=allergybot");
	}
	if (isset($_GET['adda']))
	{
		$user->add_allergy_to_user($id, $_GET['adda']);
		header("location:index.php?p=allergy&a=".$_GET['adda']);
	}
	if (isset($_GET['dela']))
	{
		$user->delete_allergy_from_user($id, $_GET['dela']);
		header("location:index.php?p=allergy&a=".$_GET['dela']);
	}
	if (isset($_GET['adds']))
	{
		$user->add_sign_to_user($id, $_GET['adds']);
		header("location:index.php?p=profile");
	}
	if (isset($_GET['dels']))
	{
		$user->delete_sign_from_user($id, $_GET['dels']);
		header("location:index.php?p=profile");
	}
	if (isset($_GET['addc']))
	{
		$user->add_cause_to_user($id, $_GET['addc']);
		header("location:index.php?p=profile");
	}
	if (isset($_GET['delc']))
	{
		$user->delete_cause_from_user($id, $_GET['delc']);
		header("location:index.php?p=profile");
	}
	if ($user->get_session())
	{
		if(isset($_GET['p'])) $p = $_GET['p'];
?>
<!DOCTYPE html>
<html lang="ro">

<head>
	<meta charset="utf-8">
	<meta name="theme-color" content="#5fcf80">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Discode">
	<title>AllergyHelp</title>

	<link rel="icon" type="image/x-icon" href="assets/img/icon.png" />
	<link href="assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/fontawesome/fontawesome.min.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">

	<script src="assets/js/jquery/jquery.min.js"></script>
	<script src="assets/js/popper/popper.min.js"></script>
	<script src="assets/js/bootstrap/bootstrap.min.js"></script>
	<script src="assets/js/main.js"></script>
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-dark">
		<div class="container">
			<button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="#navbarNav"
				aria-expanded="false" aria-label="Toggle navigation">
				<span class="icon-bar top-bar"></span>
				<span class="icon-bar middle-bar"></span>
				<span class="icon-bar bottom-bar"></span>
			</button>
			<a class="navbar-brand" href=".">
				<img src="assets/img/logo-green.png" />
			</a>
			<div class="nav-item dropdown notification-panel d-lg-none">
				<a href="#" id="notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fa fa-bell"></i>
					<?php
						if($user->count_notifications($id))
							echo '<span class="unread-badge">'.$user->count_notifications($id).'</span>';
					?>
				</a>
				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="notifications">
					<div class="notifications-header">Notificări</div>
					<div class="notifications-body">
						<?php $user->get_notifications($id); ?>
					</div>
				</div>
			</div>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "myallergies") echo ' active'; ?>" href="?p=myallergies">Alergiile mele</a></li>
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "allallergies") echo ' active'; ?>" href="?p=allallergies">Toate alergiile</a></li>
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "profile") echo ' active'; ?>" href="?p=profile">Profil</a></li>
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "messages") echo ' active'; ?>" href="?p=messages">Mesaje</a></li>
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "allergybot") echo ' active'; ?>" href="?p=allergybot">AllergyBot</a></li>
					<?php if($user->isadmin($id)) echo '<li class="nav-item"><a class="nav-link admin-panel-link" href="admin/">Admin</a></li>'; ?>
					<li class="nav-item dropdown notification-panel d-none d-lg-block">
						<a href="#" id="notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fa fa-bell"></i>
							<?php
								if($user->count_notifications($id))
									echo '<span class="unread-badge">'.$user->count_notifications($id).'</span>';
							?>
						</a>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="notifications">
							<div class="notifications-header">Notificări</div>
							<div class="notifications-body">
								<?php $user->get_notifications($id); ?>
							</div>
						</div>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php echo $user->get_firstname($id); ?>
						</a>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
							<a class="dropdown-item" href="?p=account"><i class="fa fa-fw fa-cog"></i> Setări cont</a>
							<a class="dropdown-item" href="?q=logout"><i class="fa fa-fw fa-sign-out-alt"></i> Delogare</a>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<?php
		if(empty($p)) // Pagina principala
		{
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Bine ai venit, <?php echo $user->get_firstname($id); ?>!</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<h3 class="title">Ultimele alergii înregistrate</h3>
				<?php echo $user->get_last_allergies(); ?>
				<h3 class="title mt-4">Cele mai frecvente alergii</h3>
				<?php echo $user->get_frequent_allergies(); ?>
			</div>
		</div>
	</div>
	<?php
		}
		else if($p === "allergy")
		{
			if(empty($_GET["a"]))
				header("location:index.php?p=allallergies");
			else if(!$user->allergy_exists($_GET["a"]))
				header("location:index.php?p=allallergies");
			else
			{
				$a = $_GET["a"];
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title"><?php echo $user->get_allergy_name($a); ?></h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<div class="row">
					<div class="col-md-9 mb-4">
						<?php echo $user->get_allergy_content($a); ?>
					</div>
					<div class="col-md-3">
						<div class="author">
							<img src="<?php echo $user->get_avatar($user->get_allergy_author($a)); ?>" class="avatar" />
							<strong><?php echo $user->get_firstname($user->get_allergy_author($a)).' '.$user->get_lastname($user->get_allergy_author($a)); ?></strong>
							<br /><?php echo $user->time_passed($user->get_allergy_date($a)); ?>
						</div>
						<hr />
						<?php
							if($user->is_allergy_added_to_user($id, $a))
								echo '<a href="?dela='.$a.'" class="font-weight-bold text-danger"><i class="fa fa-fw fa-minus"></i> Șterge articolul de la favorite</a>';
							else
								echo '<a href="?adda='.$a.'" class="font-weight-bold"><i class="fa fa-fw fa-plus"></i> Adaugă articolul la favorite</a>';
						?>
						<hr />
						<div class="categories categories-post">
							<h4>Simptome</h4>
							<?php $user->get_allergy_signs($a); ?>
							<h4>Cauze</h4>
							<?php $user->get_allergy_causes($a); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
			}
		}
		else if($p === "myallergies")
		{
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Alergiile mele</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<h3 class="title">Articole favorite</h3>
				<?php echo $user->get_favorite_allergies($id); ?>
				<h3 class="title">Alergii ce conțin simptomele/cauzele salvate</h3>
				<?php echo $user->get_recommended_allergies($id); ?>
			</div>
		</div>
	</div>
	<?php
		}
		else if($p === "allallergies")
		{
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Toate alergiile</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<?php $user->get_all_allergies(); ?>
			</div>
		</div>
	</div>
	<?php
		}
		else if($p === "profile")
		{
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Profil</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<div class="row">
					<div class="col-sm">
						<h3 class="title">Selectează simptomele</h3>
						<?php $user->get_signs_for_user($id); ?>
					</div>
					<div class="col-sm">
						<h3 class="title">Selectează cauzele</h3>
						<?php $user->get_causes_for_user($id); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
		}
		else if($p === "messages")
		{
			if(isset($_GET['m'])) $m = $_GET['m'];
			if(empty($m))
			{
				if (isset($_SESSION['allergyhelp_send_message_success']))
				{
					echo '
					<div class="alert alert-success alert-dismissible fade show error">
						<div class="container">
							<div class="alert-icon">
								<i class="fas fa-check-circle"></i>
							</div>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true"><i class="fas fa-times"></i></span>
							</button>
							Mesajul tău a fost trimis administratorilor! Vei primi un răspuns în cel mai scurt timp posibil.
						</div>
					</div>
					';
					$_SESSION['allergyhelp_send_message_success'] = false;
					unset($_SESSION['allergyhelp_send_message_success']);
				}
				if (isset($_SESSION['allergyhelp_send_message_fail']))
				{
					echo '
					<div class="alert alert-danger alert-dismissible fade show error">
						<div class="container">
							<div class="alert-icon">
								<i class="fas fa-exclamation-circle"></i>
							</div>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true"><i class="fas fa-times"></i></span>
							</button>
							A apărut o problemă la trimiterea mesajului!
						</div>
					</div>
					';
					$_SESSION['allergyhelp_send_message_fail'] = false;
					unset($_SESSION['allergyhelp_send_message_fail']);
				}
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Mesaje</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<h2 class="title mb-4">Mesaj nou</h2>
				<form action="" method="post" name="new_message">
					<div class="form-group row">
						<label for="subject" class="form-control-label col-sm-2 col-form-label">Subiect</label>
						<div class="col-sm">
							<input type="text" id="subject" name="subject" class="form-control" pattern=".{3,128}" maxlength="128" title="Subiectul mesajului trebuie să fie cuprins între 3 și 128 de caractere." required></input>
						</div>
					</div>
					<div class="form-group row">
						<label for="message" class="form-control-label col-sm-2 col-form-label">Mesaj</label>
						<div class="col-sm">
							<textarea class="form-control" id="message" name="message" rows="10" required></textarea>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-2"></div>
						<div class="col-sm">
							<button type="submit" class="btn btn-primary" name="new_message">Trimite</button>
						</div>
					</div>
				</form>
				<?php $user->get_conversations($id); ?>
			</div>
		</div>
	</div>
	<?php
			}
			else
			{
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Mesaje</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<?php $user->get_conversation($id, $m); ?>
			</div>
		</div>
	</div>
	<?php 
			}
		}
		else if($p === "allergybot")
		{
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">AllergyBot</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<?php $user->get_bot_conversation($id); ?>
			</div>
		</div>
	</div>
	<?php
		}
		else if($p === "account")
		{
			if (isset($_SESSION['allergyhelp_change_pass_success']))
			{
				echo '
				<div class="alert alert-success alert-dismissible fade show error" style="top: 155px;">
					<div class="container">
						<div class="alert-icon">
							<i class="fas fa-check-circle"></i>
						</div>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true"><i class="fas fa-times"></i></span>
						</button>
						Parola ta a fost schimbată!
					</div>
				</div>
				';
				$_SESSION['allergyhelp_change_pass_success'] = false;
				unset($_SESSION['allergyhelp_change_pass_success']);
			}
			if (isset($_SESSION['allergyhelp_change_pass_fail']))
			{
				echo '
				<div class="alert alert-danger alert-dismissible fade show error" style="top: 155px;">
					<div class="container">
						<div class="alert-icon">
							<i class="fas fa-exclamation-circle"></i>
						</div>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true"><i class="fas fa-times"></i></span>
						</button>
						A apărut o problemă la schimbarea parolei!
					</div>
				</div>
				';
				$_SESSION['allergyhelp_change_pass_fail'] = false;
				unset($_SESSION['allergyhelp_change_pass_fail']);
			}
			if (isset($_SESSION['allergyhelp_editp_user_success']))
			{
				echo '
				<div class="alert alert-success alert-dismissible fade show error">
					<div class="container">
						<div class="alert-icon">
							<i class="fas fa-check-circle"></i>
						</div>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true"><i class="fas fa-times"></i></span>
						</button>
						Datele tale au fost modificate!
					</div>
				</div>
				';
				$_SESSION['allergyhelp_editp_user_success'] = false;
				unset($_SESSION['allergyhelp_editp_user_success']);
			}
			if (isset($_SESSION['allergyhelp_editp_user_fail']))
			{
				echo '
				<div class="alert alert-danger alert-dismissible fade show error">
					<div class="container">
						<div class="alert-icon">
							<i class="fas fa-exclamation-circle-circle"></i>
						</div>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true"><i class="fas fa-times"></i></span>
						</button>
						Adresa de email specificată există deja!
					</div>
				</div>
				';
				$_SESSION['allergyhelp_editp_user_fail'] = false;
				unset($_SESSION['allergyhelp_editp_user_fail']);
			}
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Setări cont</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				<form action="" method="post" name="editp_user">
					<div class="form-group row">
						<label for="editp-password" class="form-control-label col-sm-2 col-form-label">Parolă nouă:</label>
						<div class="col-sm-10">
							<input type="password" class="form-control" name="editp_password" id="editp-password" placeholder="Completează doar dacă vrei să-ți schimbi parola" pattern=".{6,}" title="Parola trebuie să aibă minim 6 caractere.">
						</div>
					</div>
					<hr />
					<div class="form-group row">
						<label for="editp-email" class="form-control-label col-sm-2 col-form-label">Email:</label>
						<div class="col-sm-10">
							<input type="email" class="form-control" name="editp_email" id="editp-email" placeholder="Adresă de email" value="<?php echo $user->get_email($id); ?>" required>
						</div>
					</div>
					<div class="form-group row">
						<label for="editp-lastname" class="form-control-label col-sm-2 col-form-label">Nume:</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="editp_lastname" id="editp-lastname" placeholder="Nume" value="<?php echo $user->get_lastname($id); ?>" required>
						</div>
					</div>
					<div class="form-group row">
						<label for="editp-firstname" class="form-control-label col-sm-2 col-form-label">Prenume:</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="editp_firstname" id="editp-firstname" placeholder="Prenume" value="<?php echo $user->get_firstname($id); ?>" required>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-2"></div>
						<div class="col-sm-10">
							<button class="btn btn-sm btn-primary" type="submit" name="editp_user">Salvează modificările</button>
							<button class="btn btn-sm btn-danger" type="reset">Resetează câmpurile</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
		}
	?>
	<footer class="text-center m-2">
		<small>Realizat pentru <strong><a href="https://fiicode.asii.ro/" target="_blank" rel="noopener">FIICode 2018</a></strong> de către echipa <strong>Discode</strong>.</small>
	</footer>
</body>

</html>
<?php
	}
	else
	{
?>
<!DOCTYPE html>
<html lang="ro">

<head>
	<meta charset="utf-8">
	<meta name="theme-color" content="#5fcf80">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Discode">
	<title>AllergyHelp</title>

	<link rel="icon" type="image/x-icon" href="assets/img/icon.png" />
	<link href="assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/fontawesome/fontawesome.min.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">

	<script src="assets/js/jquery/jquery.min.js"></script>
	<script src="assets/js/popper/popper.min.js"></script>
	<script src="assets/js/bootstrap/bootstrap.min.js"></script>
	<script src="assets/js/main.js"></script>
</head>

<body>
	<div class="modal fade" id="modal_register">
		<div class="modal-dialog"  role="document">
			<form action="" method="post" name="register">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Înregistrare</h5>
					</div>
					<div class="modal-body">
						<div class="form-group row">
							<label for="reg-email" class="form-control-label col-sm-2 col-form-label">Email</label>
							<div class="col-sm-10">
								<input type="email" class="form-control" name="reg_email" id="reg-email" placeholder="Introdu adresa ta de email validă" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="reg-password" class="form-control-label col-sm-2 col-form-label">Parolă</label>
							<div class="col-sm-10">
								<input type="password" class="form-control" name="reg_password" id="reg-password" placeholder="Parola trebuie să aibă minim 6 caractere" pattern=".{6,}" required title="Parola trebuie să aibă minim 6 caractere.">
							</div>
						</div>
						<div class="form-group row">
							<label for="reg-lastname" class="form-control-label col-sm-2 col-form-label">Nume</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="reg_lastname" id="reg-lastname" placeholder="Introdu numele" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="reg-firstname" class="form-control-label col-sm-2 col-form-label">Prenume</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="reg_firstname" id="reg-firstname" placeholder="Introdu prenumele" required>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-sm btn-primary ml-auto font-weight-bold" type="submit" name="register">Înregistrează-te</button>
						<button class="btn btn-sm btn-secondary mr-auto" data-dismiss="modal">Înapoi</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="modal fade" id="modal_login">
		<div class="modal-dialog"  role="document">
			<form action="" method="post" name="login">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Autentificare</h5>
					</div>
					<div class="modal-body">
						<div class="form-group row">
							<label for="log-email" class="form-control-label col-sm-2 col-form-label">Email</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="email" id="log-email" required>
							</div>
						</div>
						<div class="form-group row">
							<label for="log-password" class="form-control-label col-sm-2 col-form-label">Parolă</label>
							<div class="col-sm-10">
								<input type="password" class="form-control" name="password" id="log-password" required>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-sm btn-primary ml-auto font-weight-bold" type="submit" name="login">Autentificare</button>
						<button class="btn btn-sm btn-secondary mr-auto" data-dismiss="modal">Înapoi</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<nav class="navbar navbar-expand-lg navbar-dark">
		<div class="container">
			<button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="#navbarNav"
				aria-expanded="false" aria-label="Toggle navigation">
				<span class="icon-bar top-bar"></span>
				<span class="icon-bar middle-bar"></span>
				<span class="icon-bar bottom-bar"></span>
			</button>
			<a class="navbar-brand" href=".">
				<img src="assets/img/logo-green.png" />
			</a>
			<div class="nav-item login-icon d-lg-none">
				<a data-toggle="modal" data-target="#modal_login">
					<i class="fas fa-sign-in-alt"></i>
				</a>
			</div>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item"><a class="nav-link link-scroll" href="#introducere">Introducere</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#alergie">Alergie</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#statistici">Statistici</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#noutati">Noutăți</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#echipa">Echipa</a></li>
					<li class="nav-item d-none d-lg-block">
						<a class="nav-link" id="login-button" data-toggle="modal" data-target="#modal_login">Autentificare</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<?php
		if (isset($_SESSION['allergyhelp_register_fail']))
		{
			echo '
			<div class="alert alert-danger alert-dismissible fade show error">
				<div class="container">
					<div class="alert-icon">
						<i class="fas fa-exclamation-circle"></i>
					</div>
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true"><i class="fas fa-times"></i></span>
					</button>
					Există deja un cont cu aceeași adresă de email!
				</div>
			</div>
			';
			$_SESSION['allergyhelp_register_fail'] = false;
			unset($_SESSION['allergyhelp_register_fail']);
		}
		if (isset($_SESSION['allergyhelp_login_fail']))
		{
			echo '
			<div class="alert alert-danger alert-dismissible fade show error">
				<div class="container">
					<div class="alert-icon">
						<i class="fas fa-exclamation-circle"></i>
					</div>
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true"><i class="fas fa-times"></i></span>
					</button>
					Adresa de email sau parola este greșită!
				</div>
			</div>
			';
			$_SESSION['allergyhelp_login_fail'] = false;
			unset($_SESSION['allergyhelp_login_fail']);
		}
	?>
	<div class="page-header page-header-filter" data-parallax="true">
		<div class="container">
			<div class="row">
				<div class="col-lg-9 col-xl-6">
					<h1 class="title">Protejează-te împotriva alergiilor</h1>
					<h4>Cu ajutorul nostru poți afla ultimele noutăți despre alergiile de orice tip, inclusiv despre simptomele și cauzele acestora. Doar selectezi simptomele și vei primi automat informații!</h4>
					<br />
					<a class="btn-get-started" data-toggle="modal" data-target="#modal_register">Înscrie-te acum</a>
				</div>
			</div>
		</div>
	</div>
	<div class="main">
		<div class="section" id="introducere">
			<div class="container text-center">
				<div class="row">
					<div class="col-md-9 ml-auto mr-auto">
						<h2 class="title">Ce reprezintă AllergyHelp?</h2>
						<h5 class="description">AllergyHelp reprezintă o platformă bazată pe o bază de date a alergiilor ce poate oferi diverse informații despre aceasta. Datorită acestei platforme, aveți acces la următoarele informații:</h5>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="info">
							<i class="fa fa-allergies text-success"></i>
							<h4 class="info-title">Baza de date a alergiilor</h4>
							<p>Odată înregistrat pe site, vei avea acces la întreaga bază de date a alergiilor ce se actualizează periodic.</p>
						</div>
					</div>
					<div class="col-md-4">
						<div class="info">
							<i class="fas fa-briefcase-medical text-danger"></i>
							<h4 class="info-title">Tratamente</h4>
							<p>Pentru fiecare alergie oferim și câte un tratament recomandat de doctori și farmaciști.</p>
						</div>
					</div>
					<div class="col-md-4">
						<div class="info">
							<i class="fa fa-comment-dots text-info"></i>
							<h4 class="info-title">Suport</h4>
							<p>Dacă nu găsești informația dorită pe AllergyHelp, ai posibilitatea de a ne contacta pentru ajutor.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="section" id="alergie">
			<div class="container">
				<div class="card card-plain card-blog">
					<div class="row">
						<div class="col-md-5">
							<div class="card-header card-header-image">
								<img class="img" src="assets/img/alergie.jpg">
								<div class="colored-shadow" style="background-image: url('assets/img/alergie.jpg'); opacity: 1;"></div>
							</div>
						</div>
						<div class="col-md-7">
							<h3 class="card-title">
								Ce sunt alergiile?
							</h3>
							<p class="card-description">
								Alergiile reprezintă o reacțe anormală a organismului la particulele pe care le consideră străine. Aceste particule sunt cunoscute drept factori alergeni și pot fi substanțe toxice (produse petrochimice, gaze de eșapament) sau nontoxice (polen, proteinele din lapte de vacă). Persoanele sensibile reacționează la cantități mici din aceste substanțe, care sunt inofensive pentru majoritatea oamenilor.
								<br />Alergiile sunt boli cronice, care se agravează din când în când și pot evolua și cu exacerbări severe. Prinicipalele manifestări ale alergiilor sunt: strănut, secreție nazală abundentă, lăcrimare excesivă, respirație dificilă.
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="tip">
				<div class="container">
					<strong>Bine de știut!</strong>
					<br /> Alergia este cea mai comună boală cronică din Europa. Până la 20% dintre pacienții cu alergii luptă în fiecare zi cu frica de o posibilă criză de astm, un șoc anafilatic sau chiar moartea de la o reacție alergică.
				</div>
			</div>
		</div>
		<div class="section section-image" id="statistici" style="background-image: url('assets/img/crowded-station.jpg');">
			<div class="container text-center">
				<h2 class="title text-center">Statistici</h2>
				<div class="row">
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">Alergiile</h3>
						Sunt a șasea cauză a bolilor cronice din USA.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">La nivel mondial</h3>
						Rinita alergică afectează între 10% și 30% din populație.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">Costurile</h3>
						Anuale ale alergiilor depășesc 18 miliarde de dolari.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">Peste 170 de alimente</h3>
						Au fost raportate ca factori alergici.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">La fiecare 3 minute</h3>
						O reacție alergică alimentară trimite pe cineva în camera de urgențe.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">În 2015</h3>
						Peste 8.8 milioane de copii au avut alergii de piele.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">În România</h3>
						Numărul alergicilor la ambrozie s-a dublat în ultimii 2-3 ani.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">Reacțiile de tip anafilatic</h3>
						Apar la aproximativ 1 din 1000 oameni din populația generală.
					</div>
					<div class="col-lg-4 col-md-6">
						<h3 class="title m-0">Studiile arată că</h3>
						În urmatorii 5-7 ani unul din doi copii vor prezenta manifestări alergice.
					</div>
				</div>
			</div>
		</div>
		<div class="section" id="noutati">
			<div class="container">
				<h2 class="title text-center">Noutăți</h2>
				<div class="row">
					<?php $user->get_last_allergies_landing(); ?>
				</div>
			</div>
		</div>
		<div class="section" id="echipa">
			<div class="container text-center">
			<div class="row">
					<div class="col-md-10 ml-auto mr-auto">
						<h2 class="title">Echipa Discode</h2>
						<h5 class="description">Noi suntem <strong>Discode</strong>, o echipă formată din patru elevi de la <strong>Liceul Teoretic de Informatică "Grigore Moisil" Iași</strong>.</h5>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6 col-md-3">
						<div class="card card-profile card-plain">
							<div class="card-header card-avatar">
								<img class="img" src="<?php echo $user->get_avatar(2); ?>">
							</div>
							<div class="card-body">
								<h4 class="card-title"><?php echo $user->get_firstname(2)." ".$user->get_lastname(2); ?></h4>
							</div>
							<div class="card-footer justify-content-center">
								<a href="https://www.facebook.com/octavzz" target="_blank" rel="noopener"><i class="fab fa-facebook-square"></i></a>
								<a href="https://www.instagram.com/octavzz" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
								<a href="https://github.com/Octavzz" target="_blank" rel="noopener"><i class="fab fa-github"></i></a>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3">
						<div class="card card-profile card-plain">
							<div class="card-header card-avatar">
								<img class="img" src="<?php echo $user->get_avatar(4); ?>">
							</div>
							<div class="card-body">
								<h4 class="card-title"><?php echo $user->get_firstname(4)." ".$user->get_lastname(4); ?></h4>
							</div>
							<div class="card-footer justify-content-center">
								<a href="https://www.facebook.com/parascheva.negru" target="_blank" rel="noopener"><i class="fab fa-facebook-square"></i></a>
								<a href="https://www.instagram.com/paraschevanegru" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
								<a href="https://github.com/paraschevanegru" target="_blank" rel="noopener"><i class="fab fa-github"></i></a>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3">
						<div class="card card-profile card-plain">
							<div class="card-header card-avatar">
								<img class="img" src="<?php echo $user->get_avatar(3); ?>">
							</div>
							<div class="card-body">
								<h4 class="card-title"><?php echo $user->get_firstname(3)." ".$user->get_lastname(3); ?></h4>
							</div>
							<div class="card-footer justify-content-center">
								<a href="https://www.facebook.com/claudiu.scurtu.3" target="_blank" rel="noopener"><i class="fab fa-facebook-square"></i></a>
								<a href="https://www.instagram.com/claudiuscurtu" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
								<a href="https://github.com/isoon5" target="_blank" rel="noopener"><i class="fab fa-github"></i></a>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3">
						<div class="card card-profile card-plain">
							<div class="card-header card-avatar">
								<img class="img" src="<?php echo $user->get_avatar(1); ?>">
							</div>
							<div class="card-body">
								<h4 class="card-title"><?php echo $user->get_firstname(1)." ".$user->get_lastname(1); ?></h4>
							</div>
							<div class="card-footer justify-content-center">
								<a href="https://www.facebook.com/alexandru.toderica" target="_blank" rel="noopener"><i class="fab fa-facebook-square"></i></a>
								<a href="https://www.instagram.com/toderrrica" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
								<a href="https://github.com/toderica" target="_blank" rel="noopener"><i class="fab fa-github"></i></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="text-center m-2">
		<small>Realizat pentru <strong><a href="https://fiicode.asii.ro/" target="_blank" rel="noopener">FIICode 2018</a></strong> de către echipa <strong>Discode</strong>.</small>
	</footer>
</body>

</html>

<?php
	}
?>
