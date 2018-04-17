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
			$user->check_login($reg_password, $reg_password);
			header("location:index.php");
		}
		else $_SESSION['allergyhelp_register_fail'] = true;
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
			<a class="navbar-brand" href=".">
				<img src="assets/img/logo-green.png" />
			</a>
			<button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="#navbarNav"
				aria-expanded="false" aria-label="Toggle navigation">
				<span class="icon-bar top-bar"></span>
				<span class="icon-bar middle-bar"></span>
				<span class="icon-bar bottom-bar"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "myallergies") echo ' active'; ?>" href="?p=myallergies">Alergiile mele</a></li>
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "allallergies") echo ' active'; ?>" href="?p=allallergies">Toate alergiile</a></li>
					<li class="nav-item"><a class="nav-link<?php if ((isset($p) ? $p : null) == "profile") echo ' active'; ?>" href="?p=profile">Profil</a></li>
					<?php if($user->isadmin($id)) echo '<li class="nav-item"><a class="nav-link admin-panel-link" href="admin/">Admin</a></li>'; ?>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<img class="avatar" src="<?php echo $user->get_avatar($id); ?>"><?php echo $user->get_firstname($id); ?>
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
				<div class="row">
					<div class="col-lg last-allergies">
						<h3 class="title">Ultimele alergii înregistrate</h3>
						<div class="card card-plain">
							<div class="row">
								<div class="col-sm-5">
									<div class="card-header card-header-image">
										<img class="img" src="assets/img/rsz_alergia-la-polen.jpg">
										<div class="colored-shadow" style="background-image: url('assets/img/rsz_alergia-la-polen.jpg'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-sm-7">
									<h4 class="card-title">
										Rinita alergică
									</h4>
									<p class="card-description">
										Cel puțin 1 din 10 persoane la nivel mondial suferă de rinită alergică, iar incidența acestei afecțiuni este în creștere, din cauza creșterii expunerii la poluanții atmosferici și utilizării crescute de antibiotice pentru tratarea infecțiilor respiratorii în copilarie.
									</p>
								</div>
							</div>
						</div>
						<div class="card card-plain">
							<div class="row">
								<div class="col-sm-5">
									<div class="card-header card-header-image">
										<img class="img" src="assets/img/astm1.jpg">
										<div class="colored-shadow" style="background-image: url('assets/img/astm1.jpg'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-sm-7">
									<h4 class="card-title">
										Astmul bronșic
									</h4>
									<p class="card-description">
										Peste 300 de milioane de oameni din întreaga lume sufera de astm bronșic, prevalența acestei boli este în creștere. În România, prevalența astmului este de 4-6%, fiind afectate aproximativ un milion de persoane, din care se estimeaza ca 60% sunt la copii și tineri.
									</p>
								</div>
							</div>
						</div>
						<div class="card card-plain">
							<div class="row">
								<div class="col-sm-5">
									<div class="card-header card-header-image">
										<img class="img" src="assets/img/rsz_dermatita.jpg">
										<div class="colored-shadow" style="background-image: url('assets/img/rsz_dermatita.jpg'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-sm-7">
									<h4 class="card-title">
										Dermatita atopică
									</h4>
									<p class="card-description">
										Eczema este cea mai comună manifestare a bolii atopice. Aceasta apare încă din copilarie și se ameliorează cu vârsta în 80% din cauzuri. Este o afecțiune cutantă inflamatorie, care creează o senzație intensă de mâncărime a pielii.
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg last-allergies">
						<h3 class="title">Cele mai frecvente alergii</h3>
						<div class="card card-plain">
							<div class="row">
								<div class="col-sm-5">
									<div class="card-header card-header-image">
										<img class="img" src="assets/img/rsz_alergia-la-polen.jpg">
										<div class="colored-shadow" style="background-image: url('assets/img/rsz_alergia-la-polen.jpg'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-sm-7">
									<h4 class="card-title">
										Rinita alergică
									</h4>
									<p class="card-description">
										Cel puțin 1 din 10 persoane la nivel mondial suferă de rinită alergică, iar incidența acestei afecțiuni este în creștere, din cauza creșterii expunerii la poluanții atmosferici și utilizării crescute de antibiotice pentru tratarea infecțiilor respiratorii în copilarie.
									</p>
								</div>
							</div>
						</div>
						<div class="card card-plain">
							<div class="row">
								<div class="col-sm-5">
									<div class="card-header card-header-image">
										<img class="img" src="assets/img/astm1.jpg">
										<div class="colored-shadow" style="background-image: url('assets/img/astm1.jpg'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-sm-7">
									<h4 class="card-title">
										Astmul bronșic
									</h4>
									<p class="card-description">
										Peste 300 de milioane de oameni din întreaga lume sufera de astm bronșic, prevalența acestei boli este în creștere. În România, prevalența astmului este de 4-6%, fiind afectate aproximativ un milion de persoane, din care se estimeaza ca 60% sunt la copii și tineri.
									</p>
								</div>
							</div>
						</div>
						<div class="card card-plain">
							<div class="row">
								<div class="col-sm-5">
									<div class="card-header card-header-image">
										<img class="img" src="assets/img/rsz_dermatita.jpg">
										<div class="colored-shadow" style="background-image: url('assets/img/rsz_dermatita.jpg'); opacity: 1;"></div>
									</div>
								</div>
								<div class="col-sm-7">
									<h4 class="card-title">
										Dermatita atopică
									</h4>
									<p class="card-description">
										Eczema este cea mai comună manifestare a bolii atopice. Aceasta apare încă din copilarie și se ameliorează cu vârsta în 80% din cauzuri. Este o afecțiune cutantă inflamatorie, care creează o senzație intensă de mâncărime a pielii.
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
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
				continutul paginii
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
				continutul paginii
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
				continutul paginii
			</div>
		</div>
	</div>
	<?php
		}
		else if($p === "account")
		{
	?>
	<div class="page-header page-header-logged page-header-filter" data-parallax="true">
		<div class="container text-center">
			<h1 class="title">Setări cont</h1>
		</div>
	</div>
	<div class="main main-logged">
		<div class="section section-logged">
			<div class="container">
				continutul paginii
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
	<nav class="navbar navbar-expand-lg navbar-dark">
		<div class="container">
			<a class="navbar-brand link-top" href="#">
				<img src="assets/img/logo-green.png" />
			</a>
			<button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="#navbarNav"
			    aria-expanded="false" aria-label="Toggle navigation">
				<span class="icon-bar top-bar"></span>
				<span class="icon-bar middle-bar"></span>
				<span class="icon-bar bottom-bar"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item"><a class="nav-link link-scroll" href="#introducere">Introducere</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#alergie">Alergie</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#statistica">Statistică</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#noutati">Noutăți</a></li>
					<li class="nav-item"><a class="nav-link link-scroll" href="#echipa">Echipa</a></li>
					<li class="nav-item dropdown">
						<a class="nav-link" href="#" id="login-button" id="dropdown-login" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Autentificare</a>
						<div class="dropdown-menu dropdown-menu-login dropdown-menu-right" aria-labelledby="dropdown-login">
							<form action="" method="post" name="login">
								<div class="input-group">
									<div class="input-group-prepend">
										<div class="input-group-text"><i class="fas fa-at"></i></div>
									</div>
									<input type="text" class="form-control" name="email" id="log-email" placeholder="Email" required="" autocomplete="off" autofocus="">
								</div>
								<div class="input-group">
									<div class="input-group-prepend">
										<div class="input-group-text"><i class="fas fa-key"></i></div>
									</div>
									<input type="password" class="form-control" name="password" id="log-password" placeholder="Parolă" required="">
								</div>
								<button class="btn btn-sm btn-block btn-primary font-weight-bold" type="submit" name="login"><i class="fas fa-fw fa-sign-in-alt"></i> Loghează-te</button>
							</form>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</nav>
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
		<div class="section section-image" id="statistica" style="background-image: url('assets/img/crowded-station.jpg')">
			<div class="container text-center">
				<div class="carousel slide carousel-fade" data-ride="carousel">
					<div class="carousel-inner">
						<div class="carousel-item active">
							<div class="row">
								<div class="col-md-4">
									<h3 class="title mb-0">Alergiile</h3>
									Sunt a șasea cauză a bolilor cronice din USA.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">La nivel mondial</h3>
									Rinita alergică afectează între 10% și 30% din populație.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">Costurile</h3>
									Anuale ale alergiilor depășesc 18 miliarde de dolari.
								</div>
							</div>
						</div>

						<div class="carousel-item">
							<div class="row">
								<div class="col-md-4">
									<h3 class="title mb-0">Peste 170 de alimente</h3>
									Au fost raportate ca factori alergici.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">La fiecare 3 minute</h3>
									O reacție alergică alimentară trimite pe cineva în camera de urgențe.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">În 2015</h3>
									Peste 8.8 milioane de copii au avut alergii de piele.
								</div>
							</div>
						</div>

						<div class="carousel-item">
							<div class="row">
								<div class="col-md-4">
									<h3 class="title mb-0">Până la 57% dintre alergicii la rinită</h3>
									Au insomnii, ducând la oboseală pe timpul zile, somnolență și la diminuarea funcționării cognitive.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">Reacțiile de tip anafilatic</h3>
									Apar la aproximativ 1 din 1000 oameni din populația generală.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">În 2015</h3>
									Peste 8.8 milioane de copii au avut alergii de piele.
								</div>
							</div>
						</div>

						<div class="carousel-item">
							<div class="row">
								<div class="col-md-4">
									<h3 class="title mb-0">Studiile arată că</h3>
									În urmatorii 5-7 ani unul din doi copii vor prezenta manifestări alergice.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">În Europa</h3>
									Sunt peste 33 de milioane de oameni ce au reacții alergice la ambrozie.
								</div>
								<div class="col-md-4">
									<h3 class="title mb-0">În România</h3>
									Numărul alergicilor la ambrozie s-a dublat în ultimii 2-3 ani.
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="section" id="noutati">
			<div class="container">
				<h2 class="title text-center">Noutăți</h2>
				<div class="row">
					<div class="col-md-4">
						<div class="card card-plain">
							<div class="card-header card-header-image">
								<img class="img" src="assets/img/rsz_alergia-la-polen.jpg">
								<div class="colored-shadow" style="background-image: url('assets/img/rsz_alergia-la-polen.jpg'); opacity: 1;"></div>
							</div>
							<h3 class="card-title">
								Rinita alergică
							</h3>
							<p class="card-description">
								Cel puțin 1 din 10 persoane la nivel mondial suferă de rinită alergică, iar incidența acestei afecțiuni este în creștere, din cauza creșterii expunerii la poluanții atmosferici și utilizării crescute de antibiotice pentru tratarea infecțiilor respiratorii în copilarie.
							</p>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card card-plain">
							<div class="card-header card-header-image">
								<img class="img" src="assets/img/astm1.jpg">
								<div class="colored-shadow" style="background-image: url('assets/img/astm1.jpg'); opacity: 1;"></div>
							</div>
							<h3 class="card-title">
								Astmul bronșic
							</h3>
							<p class="card-description">
								Peste 300 de milioane de oameni din întreaga lume sufera de astm bronșic, prevalența acestei boli este în creștere. În România, prevalența astmului este de 4-6%, fiind afectate aproximativ un milion de persoane, din care se estimeaza ca 60% sunt la copii și tineri.
							</p>
						</div>
					</div>
					<div class="col-md-4">
						<div class="card card-plain">
							<div class="card-header card-header-image">
								<img class="img" src="assets/img/rsz_dermatita.jpg">
								<div class="colored-shadow" style="background-image: url('assets/img/rsz_dermatita.jpg'); opacity: 1;"></div>
							</div>
							<h3 class="card-title">
								Dermatita atopică
							</h3>
							<p class="card-description">
								Eczema este cea mai comună manifestare a bolii atopice. Aceasta apare încă din copilarie și se ameliorează cu vârsta în 80% din cauzuri. Este o afecțiune cutantă inflamatorie, care creează o senzație intensă de mâncărime a pielii.
							</p>
						</div>
					</div>
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
								<img class="img" src="assets/img/avatars/2.jpg">
							</div>
							<div class="card-body">
								<h4 class="card-title">Iacob Octavian</h4>
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
								<img class="img" src="assets/img/avatars/4.jpg">
							</div>
							<div class="card-body">
								<h4 class="card-title">Negru Parascheva</h4>
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
								<img class="img" src="assets/img/avatars/3.jpg">
							</div>
							<div class="card-body">
								<h4 class="card-title">Scurtu Claudiu</h4>
							</div>
							<div class="card-footer justify-content-center">
								<a href="https://www.facebook.com/claudiu.scurtu" target="_blank" rel="noopener"><i class="fab fa-facebook-square"></i></a>
								<a href="https://www.instagram.com/claudiuscurtu" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
								<a href="https://github.com/isoon5" target="_blank" rel="noopener"><i class="fab fa-github"></i></a>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3">
						<div class="card card-profile card-plain">
							<div class="card-header card-avatar">
								<img class="img" src="assets/img/avatars/1.jpg">
							</div>
							<div class="card-body">
								<h4 class="card-title">Toderică Alexandru</h4>
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