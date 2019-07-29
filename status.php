<?php

define("enabled", true);

session_start();

$_SESSION['lasturl'] = "status.php";

DEFINE("mysql_host", "localhost");
DEFINE("mysql_user", "root");
DEFINE("mysql_database", "apps");
DEFINE("mysql_pass", "");


$mysql = new PDO('mysql:host='.mysql_host.';dbname='.mysql_database, mysql_user, mysql_pass);

$mysql->query('SET NAMES utf8');
$mysql->query('SET CHARACTER SET utf8');
$mysql->query('SET collation_connection = utf8_polish_ci');

if(isset($_POST['logout']))
{
	unset($_SESSION['user']);
	unset($_SESSION['memoryarray']);
	header("Location: https://aplikacje.strefarp.pl/index.php");
}

if(!enabled) die ("<center style='margin-top: 19%; font-size: 30px; font-weight: bold;'><h1>Aplikacje tymczasowo wstrzymane!<br>Prace techniczne!</h1></center>");
?>
<head>

	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="bstable/bootstrap-table.css">
	<link rel="stylesheet" href="css/animate.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
	<link href="lib/noty.css" rel="stylesheet">
	<meta charset="utf-8"></meta>
	<title>Aplikacje StrefaRP</title>

</head>

<body>
	<div class="row" style="margin-top: 2%">
		<div class="col-md-10 col-md-offset-1" id="errorspacer" style="min-height: 50px;">
		</div>
	</div>
	<?php if(isset($_SESSION['user']))
	{
		$user = $_SESSION['user'];
		if($check = $mysql->prepare("SELECT * FROM `apps` WHERE `discord` = ? OR `discordId` = ? ORDER BY `createDate` DESC LIMIT 5"))
		{
			$fulluser = $user['username']."#".$user['discriminator'];
			$check->execute(array($fulluser, $user['id']));
			$rows = $check->rowCount();
			if($rows > 0)
			{
				?>
				<div class="container"><h1>Witaj, <?php echo $fulluser; ?><form method="post" style="display:inline"><button type="submit" name="logout" class="btn btn-danger pull-right">Wyloguj</button></form><a href="index.php" class="btn btn-info pull-right" style="margin-right: 3px;">Nowa Aplikacja</a><h1></div>
				<?php
				$apps = $check->fetchAll();
				foreach($apps as $app)
				{
					if($app['deny'] == NULL && $app['isAccepted'] == NULL)
					{
					?>
						<div class="row" style="margin-top: 2%">
							<div class="col-md-10 col-md-offset-1">	
								<div class="panel panel-warning">
									<div class="panel-heading">
										<h3 class="panel-title"><b>Status twojej aplikacji</b><i class="fas fa-user pull-right"></i></h3>
									</div>
									<div class="panel-body">	
										<h3>Twoja aplikacja zostanie wkrótce sprawdzona! Prosimy o cierpliwość!</h3>
										<div class="well well-sm">Status Aplikacji<span class="pull-right">Oczekuje na sprawdzenie</span></div>
										<a class="btn btn-primary" role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
											Pokaż/Ukryj swoje odpowiedzi
										</a>
										<div class="collapse" id="collapseExample">
											<div class="well">
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Discord</b>: <?php echo $app['discord']; ?></h3>
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>SteamID</b>: <?php echo $app['steamId']; ?></h3>
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>ID na forum</b>: <?php echo $app['forumProfile']; ?></h3>
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Data urodzenia</b>: <?php echo explode("T", $app['birthday'])[0]; ?></h3>
												</div>

												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym dla Ciebie jest Roleplay?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['rp']); ?></p>
												</div>
												
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Podaj swoje doświadczenia z RP i opisz postacie jakie wcześniej odgrywałeś</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['pastRpCharacter']); ?></p>
												</div>
												
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakie postacie zamierzasz odgrywać na naszym serwerze?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['currentRpCharacter']); ?></p>
												</div>
												
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Streamujesz lub nagrywasz content na swoje kanały?<br>Jeśli tak, podaj linki, czy też nagrania z przykładem twojego RP.</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['stream']); ?></p>
												</div>
												
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Wyślij link do regulaminu oraz wskaż jego dobre i złe strony.</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['rules']); ?></p>
												</div>
												
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Po co są komendy /me i /do i jak ich używać?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['shop']); ?></p>
												</div>
												
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest OOC oraz IC i czym się różnią obie strefy?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['car']); ?></p>
												</div>
												
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy używając komendę /do, możesz kłamać?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['medo']); ?></p>
												</div>
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakim rodzajem czatu jest komenda /tweet i do czego jest używana?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['tweet']); ?></p>
												</div>
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy dozwolone jest zabijanie z zemsty drugiej postaci?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['revenge']); ?></p>
												</div>
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest BW? (Brutally Wounded)</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['bw']); ?></p>
												</div>
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest metagaming? I czy można go używać w grze?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['metagaming']); ?></p>
												</div>
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest powergaming?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['powergaming']); ?></p>
												</div>
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Kiedy twoja postać jest zobowiązana zapomnieć o sytuacji która miała miejsce przed po zrespieniu się w szpitalu?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['forget']); ?></p>
												</div>
												<div class="well well-sm">
													<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Co zrobisz gdy dostaniesz crasha, jak powiadomisz o tym administrację?</b></h3>
													<p style="font-size: 16px"><?php echo nl2br($app['crash']); ?></p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php
					}
					else
					{
						if($app['deny'] != NULL)
						{
					?>
							<div class="row" style="margin-top: 2%">
								<div class="col-md-10 col-md-offset-1">
									<div class="panel panel-danger">
										<div class="panel-heading">
											<h3 class="panel-title"><b>Status twojej aplikacji</b><i class="fas fa-user pull-right"></i></h3>
										</div>
										<div class="panel-body">	
											<h3>
												Twoje podanie zostało rozpatrzone!<br>
												<br>
												Z przykrością stwierdzamy, że odpowiedź jest negatywna.<br>
												Nie uzasadniamy powodów odpowiedzi gdyż trwało by to zbyt długo a chcemy aby ten proces przebiegał szybko.<br>
												<br>
												Zachęcamy do ponownego złożenia podania po upływie 7 dni od daty odrzucenia ostatniej aplikacji.
												<br><br><b>Data odrzucenia tego podania</b>: <?php 
													$date = json_decode($app['denyDate'], true); 
													if(isset($date['$deny'])) echo date("d-m-Y", strtotime($date['$deny'])); 
													if(isset($date['$date'])) echo date("d-m-Y", strtotime($date['$date'])); 
													
												?>
											</h3>
											<div class="well well-sm">Status Aplikacji<span class="pull-right">Odrzucona</span></div>
											<a class="btn btn-primary" role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
												Pokaż/Ukryj swoje odpowiedzi
											</a>
											<div class="collapse" id="collapseExample">
												<div class="well">
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Discord</b>: <?php echo $app['discord']; ?></h3>
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>SteamID</b>: <?php echo $app['steamId']; ?></h3>
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Forum</b>: <a href="<?php echo $app['forumProfile']; ?>"><?php echo $app['forumProfile']; ?></a></h3>
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Data urodzenia</b>: <?php echo explode("T", $app['birthday'])[0]; ?></h3>
													</div>

													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym dla Ciebie jest Roleplay?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['rp']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Podaj swoje doświadczenia z RP i opisz postacie jakie wcześniej odgrywałeś</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['pastRpCharacter']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakie postacie zamierzasz odgrywać na naszym serwerze?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['currentRpCharacter']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Streamujesz lub nagrywasz content na swoje kanały?<br>Jeśli tak, podaj linki, czy też nagrania z przykładem twojego RP.</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['stream']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Wyślij link do regulaminu oraz wskaż jego dobre i złe strony.</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['rules']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Sytuacja:<br>Okradasz sklep.<br>W środku znajduje się właściciel sklepu oraz cywil.<br>Obydwoje błagają abyś ich nie okradł lub nie zranił.<br>Jakbyś odegrał tą sytuację i jakie działania podjął?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['shop']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Sytuacja:<br>Naprawiasz swój samochód na środku drogi,<br>ktoś podjeżdża od tyłu i zaczyna w ciebie celować z broni palnej.<br>Każą ci podnieść ręce i oddać kluczyki do samochodu.<br>Co byś zrobił w tej sytuacji i jak się zachował?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['car']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy używając komendę /do, możesz kłamać?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['medo']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakim rodzajem czatu jest komenda /tweet i do czego jest używana?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['tweet']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy dozwolone jest zabijanie z zemsty drugiej postaci?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['revenge']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest BW? (Brutally Wounded)</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['bw']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest metagaming? I czy można go używać w grze?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['metagaming']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest powergaming?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['powergaming']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Kiedy twoja postać jest zobowiązana zapomnieć o sytuacji która miała miejsce przed po zrespieniu się w szpitalu?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['forget']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Co zrobisz gdy dostaniesz crasha, jak powiadomisz o tym administrację?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['crash']); ?></p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
					<?php
						}
						elseif($app['isAccepted'] != NULL)
						{
						?>
							<div class="row" style="margin-top: 2%">
								<div class="col-md-10 col-md-offset-1">
									<div class="panel panel-success">
										<div class="panel-heading">
											<h3 class="panel-title"><b>Status twojej aplikacji</b><i class="fas fa-user pull-right"></i></h3>
										</div>
										<div class="panel-body">	
											<h3>
												Twoje podanie zostało przyjęte!<br>
												Też cieszymy się niezmiernie!<br>
												<br>
												Niedługo otrzymasz rangę na Discord.<br>
												Do gry możesz dołączyć po restarcie serwera (zaćmieniu).<br>
												W przypadku problemu z połączeniem napisz do kogoś z supportu.
											</h3>
											<div class="well well-sm">Status Aplikacji<span class="pull-right">Przyjęta</span></div>
											<a class="btn btn-primary" role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
												Pokaż/Ukryj swoje odpowiedzi
											</a>
											<div class="collapse" id="collapseExample">
												<div class="well">
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Discord</b>: <?php echo $app['discord']; ?></h3>
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>SteamID</b>: <?php echo $app['steamId']; ?></h3>
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Forum</b>: <a href="<?php echo $app['forumProfile']; ?>"><?php echo $app['forumProfile']; ?></a></h3>
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Data urodzenia</b>: <?php echo explode("T", $app['birthday'])[0]; ?></h3>
													</div>

													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym dla Ciebie jest Roleplay?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['rp']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakie postacie odgrywałeś wcześniej?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['pastRpCharacter']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakie postacie zamierzasz odgrywać na naszym serwerze?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['currentRpCharacter']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Streamujesz lub nagrywasz content na swoje kanały?<br>Jeśli tak, podaj linki, czy też nagrania z przykładem twojego RP.</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['stream']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Wyślij link do regulaminu oraz wskaż jego dobre i złe strony.</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['rules']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Sytuacja:<br>Okradasz sklep.<br>W środku znajduje się właściciel sklepu oraz cywil.<br>Obydwoje błagają abyś ich nie okradł lub nie zranił.<br>Jakbyś odegrał tą sytuację i jakie działania podjął?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['shop']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Sytuacja:<br>Naprawiasz swój samochód na środku drogi,<br>ktoś podjeżdża od tyłu i zaczyna w ciebie celować z broni palnej.<br>Każą ci podnieść ręce i oddać kluczyki do samochodu.<br>Co byś zrobił w tej sytuacji i jak się zachował?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['car']); ?></p>
													</div>
													
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy używając komendę /do, możesz kłamać?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['medo']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakim rodzajem czatu jest komenda /tweet i do czego jest używana?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['tweet']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy dozwolone jest zabijanie z zemsty drugiej postaci?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['revenge']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest BW? (Brutally Wounded)</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['bw']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest metagaming? I czy można go używać w grze?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['metagaming']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest powergaming?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['powergaming']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Kiedy twoja postać jest zobowiązana zapomnieć o sytuacji która miała miejsce przed po zrespieniu się w szpitalu?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['forget']); ?></p>
													</div>
													<div class="well well-sm">
														<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Co zrobisz gdy dostaniesz crasha, jak powiadomisz o tym administrację?</b></h3>
														<p style="font-size: 16px"><?php echo nl2br($app['crash']); ?></p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php
						}
					}
				}
			}
			else
			{
			?>
				<div class="row" style="margin-top: 2%">
					<div class="col-md-10 col-md-offset-1">
						<h1>Witaj, <?php echo $fulluser; ?><form method="post" style="display:inline"><button type="submit" name="logout" class="btn btn-danger pull-right">Wyloguj</button></form><a href="index.php" class="btn btn-info pull-right" style="margin-right: 3px;">Nowa Aplikacja</a><h1>
						<div class="well well-sm">
							<h1><center><b>Nie znaleźliśmy zadnej aplikacji dla tego konta!</b></center></h1>
						</div>
					</div>
				</div>
			<?php
			}
		}
	}
	else
	{
	?>
		<div class="container" style="margin-top: 15%">
			<center>
				<h1>Wygląda na to, że nie jesteś zalogowany/a!</h1>
				<a href="authorize.php" class="btn btn-block btn-lg btn-info">Zaloguj sie przez Discord</a>
			</center>
		</div>
	<?php
	}
	?>
	<script src="js/jquery.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="bstable/bootstrap-table.js"></script>
	<script src="lib/noty.js" type="text/javascript"></script>
	
	<?php if(isset($_SESSION['error'])) 
	{
	?>
		<script>
		new Noty({
			type: '<?php echo $_SESSION['error_type']; ?>',
			container: '#errorspacer',
			text: '<?php echo $_SESSION['error_text']; ?>',
			timeout: 7500,
			animation: {
				open: 'animated bounceInDown', // Animate.css class names
				close: 'animated bounceOutUp' // Animate.css class names
			}
		}).show();
		</script>
	<?php
	unset($_SESSION['error']);
	unset($_SESSION['error_type']);
	unset($_SESSION['error_text']);
	}
	?>
</body>