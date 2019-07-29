<?php

session_start();

if(isset($_SESSION['user']) && isset($_POST['app_send']))
{
	DEFINE("mysql_host", "localhost");
	DEFINE("mysql_user", "root");
	DEFINE("mysql_database", "apps");
	DEFINE("mysql_pass", "");


	$mysql = new PDO('mysql:host='.mysql_host.';dbname='.mysql_database, mysql_user, mysql_pass);

	$mysql->query('SET NAMES utf8');
	$mysql->query('SET CHARACTER SET utf8');
	$mysql->query('SET collation_connection = utf8_polish_ci');
	
	
	$steam = $_POST['app_steam'];
	$forum = $_POST['app_forum'];
	$birth = $_POST['app_birthday'];
	$rp = $_POST['app_rp'];
	$pastrp = $_POST['app_pastrp'];
	$currrp = $_POST['app_currentrp'];
	$stream = $_POST['app_stream'];
	$rules = $_POST['app_rules'];
	$shop = $_POST['app_shop'];
	$car = $_POST['app_car'];
	$medo = $_POST['app_medo'];
	$tweet = $_POST['app_tweet'];
	$revenge = $_POST['app_revenge'];
	$bw = $_POST['app_bw'];
	$metagaming = $_POST['app_metagaming'];
	$powergaming = $_POST['app_powergaming'];
	$forget = $_POST['app_forget'];
	$crash = $_POST['app_crash'];
	
	$memoryarray = array($steam, $forum, $birty, $rp, $pastrp, $currrp, $stream, $rules, $shop, $car, $medo, $tweet, $revenge, $bw, $metagaming, $powergaming, $forget, $crash);
	$_SESSION['memoryarray'] = $memoryarray;
	
	if(!empty($steam) && !empty($forum) && !empty($birth) && !empty($rp) && !empty($pastrp) && !empty($currrp) && !empty($stream) && !empty($rules) && !empty($shop) && !empty($car) && !empty($medo) && !empty($tweet) && !empty($revenge) && !empty($bw) && !empty($metagaming) && !empty($powergaming) && !empty($forget) && !empty($crash))
	{
		if(strlen($steam) <= 200 && strlen($forum) <= 200 && strlen($birth) <= 32 && strlen($rp) <= 12000 && strlen($pastrp) <= 12000 && strlen($currrp) <= 12000 && strlen($stream) <= 12000 && strlen($rules) <= 12000 && strlen($shop) <= 12000 && strlen($car) <= 12000 && strlen($medo) <= 12000 && strlen($tweet) <= 12000 && strlen($revenge) <= 12000 && strlen($metagaming) <= 12000 && strlen($powergaming) <= 12000 && strlen($forget) <= 12000 && strlen($crash) <= 12000)
		{
			$user = $_SESSION['user'];
			$fullname = $user['username']."#".$user['discriminator'];
			
			if($check = $mysql->prepare("SELECT * FROM `apps` WHERE `discord` = ? AND `discordId` = ? AND `deny` IS NULL AND `isAccepted` IS NULL"))
			{
				$check->execute(array($fullname, $user['id']));
				$rows = $check->rowCount();
				if($rows <= 0)
				{
					if($insert = $mysql->prepare("INSERT INTO `apps` (`id`, `birthday`, `forumProfile`, `steamId`, `stream`, `rules`, `rp`, `pastRpCharacter`, `currentRpCharacter`, `shop`, `car`, `medo`, `tweet`, `revenge`, `bw`, `metagaming`, `powergaming`, `forget`, `crash`, `discord`, `discordId`, `email`, `createDate`, `deny`, `denyDate`, `msgSend`, `isAccepted`, `addedToWhitelist`, `isWatched`, `watchExpire`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, NULL, '0', '0')"))
					{
						$time = date("Y-m-d")."T".date("H:i:s.vO");
						$insert->execute(array($birth, $forum, $steam, $stream, $rules, $rp, $pastrp, $currrp, $shop, $car, $medo, $tweet, $revenge, $bw, $metagaming, $powergaming, $forget, $crash, $fullname, $user['id'], '-', $time));
						
						unset($memoryarray);
						$_SESSION['error'] = true;
						$_SESSION['error_type'] = 'success';
						$_SESSION['error_text'] = 'Aplikacja wysłana!<br>Czas oczekiwania na sprawdzenie: do 7 dni<br>(Czas oczekiwania zależy od ilości podań)<br><br>Prosimy o cierpliwość!';
						header("Location: https://aplikacje.strefarp.pl/index.php");
					}
					else
					{
						$_SESSION['error'] = true;
						$_SESSION['error_type'] = 'error';
						$_SESSION['error_text'] = 'Błąd formularza! Spróbuj ponownie!';
						header("Location: https://aplikacje.strefarp.pl/index.php");
					}
				}
				else
				{
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'error';
					$_SESSION['error_text'] = 'Wysłałeś/aś już aplikację!<br>Poczekaj cierpliwie na jej sprawdzenie!';
					header("Location: https://aplikacje.strefarp.pl/index.php");
				}
			}
			else
			{
				$_SESSION['error'] = true;
				$_SESSION['error_type'] = 'error';
				$_SESSION['error_text'] = 'Błąd formularza! Spróbuj ponownie!';
				header("Location: https://aplikacje.strefarp.pl/index.php");
			}
		}
		else
		{
			$fields = "";
			if(strlen($steam) > 200) $fields .= "SteamID, ";
			if(strlen($forum) > 200) $fields .= "Link do profilu forum, ";
			if(strlen($birth) > 32) $fields .= "Data urodzenia, ";
			if(strlen($rp) > 12000) $fields .= "Czym jest dla Ciebie Roleplay, ";
			if(strlen($pastrp) > 12000) $fields .= "Podaj swoje doświadczenia z RP i opisz postacie jakie wcześniej odgrywałeś, ";
			if(strlen($currrp) > 12000) $fields .= "Jakie postacie zamierzasz odgrywać na naszym serwerze?, ";
			if(strlen($stream) > 12000) $fields .= "Streamujesz lub nagrywasz content na swoje kanały, ";
			if(strlen($rules) > 12000) $fields .= "Link do regulaminu, ";
			if(strlen($shop) > 12000) $fields .= "Po co są komendy /me i /do, ";
			if(strlen($car) > 12000) $fields .= "Czym jest OOC oraz IC i czym się różnią obie strefy?, ";
			if(strlen($medo) > 12000) $fields .= "Czy używając komendę /do, możesz kłamać?, ";
			if(strlen($tweet) > 12000) $fields .= "Jakim rodzajem czatu jest komenda /tweet i do czego jest używana?, ";
			if(strlen($revenge) > 12000) $fields .= "Czy dozwolone jest zabijanie z zemsty drugiej postaci?, ";
			if(strlen($bw) > 12000) $fields .= "Czym jest BW? (Brutally Wounded), ";
			if(strlen($metagaming) > 12000) $fields .= "Czym jest metagaming? I czy można go używać w grze?, ";
			if(strlen($powergaming) > 12000) $fields .= "Czym jest powergaming?, ";
			if(strlen($forget) > 12000) $fields .= "Kiedy twoja postać jest zobowiązana zapomnieć o sytuacji która miała miejsce przed po zrespieniu się w szpitalu?, ";
			if(strlen($crash) > 12000) $fields .= "Co zrobisz gdy dostaniesz crasha, jak powiadomisz o tym administrację?, ";
			
			$fields[strlen($fields)-1] = "";
			$fields[strlen($fields)-2] = "";
			
			$_SESSION['error'] = true;
			$_SESSION['error_type'] = 'error';
			$_SESSION['error_text'] = 'Błąd podczas wysyłania aplikacji!<br><br>Pola w których przekroczyłeś/aś dozwoloną ilość znaków:<br><br><b>'.$fields.'</b>';
			header("Location: https://aplikacje.strefarp.pl/index.php");
		}
	}
	else
	{
		$_SESSION['error'] = true;
		$_SESSION['error_type'] = 'error';
		$_SESSION['error_text'] = 'Nie wszystkie pola zostaly wypełnione!';
		header("Location: https://aplikacje.strefarp.pl/index.php");
	}
}
else
{
	header("Location: https://aplikacje.strefarp.pl/index.php");
}