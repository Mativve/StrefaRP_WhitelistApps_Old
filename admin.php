<?php
session_start();

$_SESSION['lasturl'] = "admin.php";

DEFINE("mysql_host", "localhost");
DEFINE("mysql_user", "root");
DEFINE("mysql_database", "apps");
DEFINE("mysql_pass", "");

DEFINE("mysqlForum_host", "localhost");
DEFINE("mysqlForum_user", "");
DEFINE("mysqlForum_database", "");
DEFINE("mysqlForum_pass", "");


$mysql = new PDO('mysql:host='.mysql_host.';dbname='.mysql_database, mysql_user, mysql_pass);

$mysqlForum = new PDO('mysql:host='.mysqlForum_host.';dbname='.mysqlForum_database, mysqlForum_user, mysqlForum_pass);

$mysql->query('SET NAMES utf8');
$mysql->query('SET CHARACTER SET utf8');
$mysql->query('SET collation_connection = utf8_polish_ci');

$mysqlForum->query('SET NAMES utf8');
$mysqlForum->query('SET CHARACTER SET utf8');
$mysqlForum->query('SET collation_connection = utf8_polish_ci');


// acceptapp denyapp editaccept editdeny addwl
include __DIR__.'/vendor/autoload.php';
use RestCord\DiscordClient;

if(isset($_GET['logout']))
{
	unset($_SESSION['user']);
	unset($_SESSION['checkingapp']);
	unset($_SESSION['memoryarray']);
	header("Location: https://aplikacje.strefarp.pl/admin.php");
}


if(isset($_SESSION['user']))
{		
	$users = array(
	array("Nick Discord", 			"4 cyferki bez #", 			LongIDDiscorda),
	);
	$user = $_SESSION['user'];
	$found = false;
	for($i = 0; $i < count($users); $i++)
	{
		if($user['username'] == $users[$i][0] && $user['discriminator'] == $users[$i][1] && $user['id'] == $users[$i][2])
		{
			$found = true;
			break;
		}
	}
	
	if(!$found) 
	{ 
		unset($_SESSION['user']); 
		header("Refresh: 2; URL=https://strefarp.pl/");
		die ("<center style='margin-top: 19%; font-size: 30px; font-weight: bold;'><h1>Brak dostępu do tej części strony!<br><a href='https://strefarp.pl/'>Przejdź do forum!</a></h1></center>"); 
	}
	
	if(!isset($_SESSION['checkingapp']))
	{
		$_SESSION['checkingapp'] = array();
	}
	
	if(isset($_POST['checkapp']))
	{
		$id = $_POST['appid'];
		if($chk = $mysql->prepare("SELECT * FROM `apps` WHERE `id` = ?"))
		{
			$chk->execute(array($id));
			$rows = $chk->rowCount();
			if($rows > 0)
			{
				$appinfo = $chk->fetchAll();
				if($appinfo[0]['isWatched'] == 0 || $appinfo[0]['watchExpire'] <= time())
				{
					if($log = $mysql->prepare("INSERT INTO `logs` VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"))
					{
						$log->execute(array($user['username'], $user['discriminator'], $user['id'], "TAKE_APP_DISPLAY", "250", $appinfo[0]['id'], $appinfo[0]['discord']));
					}
					
					if($upd = $mysql->prepare("UPDATE `apps` SET `isWatched` = 1, `watchExpire` = ? WHERE `id` = ?"))
					{
						$time = time()+600;
						
						$upd->execute(array($time, $id));
						array_push($_SESSION['checkingapp'], $id);
						header("Location: https://aplikacje.strefarp.pl/admin.php?app=".$id);
						//echo "window.open('Location: https://aplikacje.strefarp.pl/admin.php?app=".$id."', '_blank')";
					}
				}
				else
				{
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'warning';
					$_SESSION['error_text'] = 'Ktoś już przegląda tą aplikacje!<br>Zacznij przeglądać inną tak aby nie nakładać się na siebie!';
				}
			}
		}
	}
	
	if(isset($_POST['gobacktoapps']))
	{
		$id = $_GET['app'];
		if($chk = $mysql->prepare("SELECT * FROM `apps` WHERE `id` = ?"))
		{
			$chk->execute(array($id));
			$rows = $chk->rowCount();
			if($rows > 0)
			{
				$appinfo = $chk->fetchAll();
				if($log = $mysql->prepare("INSERT INTO `logs` VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"))
				{
					$log->execute(array($user['username'], $user['discriminator'], $user['id'], "LEAVE_APP_DISPLAY", "255", $appinfo[0]['id'], $appinfo[0]['discord']));
				}
				
				if($upd = $mysql->prepare("UPDATE `apps` SET `isWatched` = 0, `watchExpire` = 0 WHERE `id` = ?"))
				{
					$upd->execute(array($id));
					$key = array_search($id, $_SESSION['checkingapp']);
					unset($_SESSION['checkingapp'][$key]);
					echo "<script>window.close();</script>";
				}
			}
		}
	}
	else
	{
		if(isset($_GET['app']))
		{
			if(!in_array($_GET['app'], $_SESSION['checkingapp']))
			{
				$_SESSION['error'] = true;
				$_SESSION['error_type'] = 'info';
				$_SESSION['error_text'] = 'Jeśli chcesz przejrzeć to podanie musisz zrobić to poprzez nacisnięcie przycisku!';
				header("Location: https://aplikacje.strefarp.pl/admin.php");
			}
		}
	}
	
	if(isset($_POST['acceptapp']))
	{
		$bot = new DiscordClient(['token' => 'TuPodajSwojTokenBota']);
		$id = $_POST['id'];
		$discord = $_POST['discord'];
		$discordid = $_POST['discordid'];
		if(filter_var($id, FILTER_VALIDATE_INT) && filter_var($discordid, FILTER_VALIDATE_INT))
		{
			if($id > 0)
			{
				
				if($discordid != -1)
				{
					$dm = $bot->user->createDm(array("recipient_id" => intval($discordid)));
					$cid = $dm->id;
				}
				
				$time = json_encode(array("\$deny" => date("Y-m-d")."T".date("H:i:s.vO")));
				
				if($log = $mysql->prepare("INSERT INTO `logs` VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"))
				{
					$log->execute(array($user['username'], $user['discriminator'], $user['id'], "ACCEPT_APP", "1", $id, $discord));
				}

				$app = $mysql->prepare("SELECT `forumProfile` FROM `apps` WHERE `id` = ?");
				$app->execute(array($id));
				$uid = $app->fetch()[0];

				if ($forumGroup = $mysqlForum->prepare("UPDATE `core_members` SET `member_group_id` = ? WHERE `member_id` = ?"))
				{
					$forumGroup->execute(array(22, $uid));
				}
				
				if($upd = $mysql->prepare("UPDATE `apps` SET `isAccepted` = 'true', `addedToWhitelist` = 'false', `msgSend` = 'false', `denyDate` = ?, `isWatched` = 0, `watchExpire` = 0 WHERE `id` = ?"))
				{
					$upd->execute(array($time, $id));
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'success';
					$_SESSION['error_text'] = 'Aplikacja o ID: '.$id.'<br>['.$discord.']<br><br>Została zaakceptowana!';
					echo "<script>window.close();</script>";
				}
				
				$key = array_search($id, $_SESSION['checkingapp']);
				unset($_SESSION['checkingapp'][$key]);
				
				if($discordid != -1)
				{
					$bot->channel->createMessage([
						'channel.id' => $cid,
						'content'    => "",
						'embed'      => [
							 "title" => "Odpowiedź na twoją aplikacje",
							 "description" => "",
							 "url" => "https://aplikacje.strefarp.pl",
							 "color" => 65403,
							 "timestamp" => "",
							 "footer" => [
								 "icon_url" => "",
								 "text" => "StrefaRP - Aplikacje"
							 ],
							 "thumbnail" => [
								 "url" => ""
							 ],
							 "image" => [
								 "url" => ""
							 ],
							 "author" => [
								 "name" => "",
								 "url" => "https://aplikacje.strefarp.pl",
								 "icon_url" => ""
							 ],
							 "fields" => [
								 [
									 "name" => "Status:",
									 "value" => "```fix\nWitamy! Twoje podanie zostało przyjęte!\nTeż cieszymy się niezmiernie!\n\nNiedługo otrzymasz rangę na Discord.\nDo gry możesz dołączyć po restarcie serwera (zaćmieniu).\nW przypadku problemu z połączeniem napisz do kogoś z supportu.\n\n\nPozdrawiamy, StrefaRP```"
								 ]
							 ]
						 ]
					]);
					
					$bot->guild->addGuildMemberRole([
						'guild.id' => IdSerwera,
						'user.id' => intval($discordid),
						'role.id' => IdGrupyWhitelist,
					]);
				}
			}
		}
	}

	if(isset($_POST['denyapp']))
	{
		$bot = new DiscordClient(['token' => 'TuPodajTokenBota']);
		$id = $_POST['id'];
		$discord = $_POST['discord'];
		$discordid = $_POST['discordid'];
		if(filter_var($id, FILTER_VALIDATE_INT) && filter_var($discordid, FILTER_VALIDATE_INT))
		{
			if($id > 0)
			{
				if($discordid != -1)
				{
					$dm = $bot->user->createDm(array("recipient_id" => intval($discordid)));
					$cid = $dm->id;
				}
				
				$time = json_encode(array("\$deny" => date("Y-m-d")."T".date("H:i:s.vO")));
				
				if($log = $mysql->prepare("INSERT INTO `logs` VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"))
				{
					$log->execute(array($user['username'], $user['discriminator'], $user['id'], "DENY_APP", "2", $id, $discord));
				}
				
				if($upd = $mysql->prepare("UPDATE `apps` SET `deny` = 'true', `denyDate` = ?, `msgSend` = 'false', `isWatched` = 0, `watchExpire` = 0 WHERE `id` = ?"))
				{
					$upd->execute(array($time, $id));
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'error';
					$_SESSION['error_text'] = 'Aplikacja o ID: '.$id.'<br>['.$discord.']<br><br>Została odrzucona!';
					echo "<script>window.close();</script>";
				}
				
				$key = array_search($id, $_SESSION['checkingapp']);
				unset($_SESSION['checkingapp'][$key]);
				
				if($discordid != -1)
				{
					$bot->channel->createMessage([
						'channel.id' => $cid,
						'content'    => "",
						'embed'      => [
							 "title" => "Odpowiedź na twoją aplikacje",
							 "description" => "",
							 "url" => "https://aplikacje.strefarp.pl",
							 "color" => 14290439,
							 "timestamp" => "",
							 "footer" => [
								 "icon_url" => "",
								 "text" => "StrefaRP - Aplikacje"
							 ],
							 "thumbnail" => [
								 "url" => ""
							 ],
							 "image" => [
								 "url" => ""
							 ],
							 "author" => [
								 "name" => "",
								 "url" => "https://aplikacje.strefarp.pl",
								 "icon_url" => ""
							 ],
							 "fields" => [
								 [
									 "name" => "Status:",
									 "value" => "```fix\nWitam, Twoje podanie zostało rozpatrzone!\n\nZ przykrością stwierdzamy, że odpowiedź jest negatywna.\nNie uzasadniamy powodów odpowiedzi gdyż trwało by to zbyt długo a chcemy aby ten proces przebiegał szybko.\n\nZachęcamy do ponownego złożenia podania po upływie 7 dni od momentu otrzymania tej wiadomości.\n\n\nPozdrawiamy, StrefaRP```"
								 ]
							 ]
						 ]
					]);
				}
			}
		}
	}

	if(isset($_POST['editaccept']))
	{
		$id = $_POST['id'];
		$discord = $_POST['discord'];
		$discordid = $_POST['discordid'];
		if(filter_var($id, FILTER_VALIDATE_INT) && filter_var($discordid, FILTER_VALIDATE_INT))
		{
			if($id > 0)
			{
				$time = json_encode(array("\$deny" => date("Y-m-d")."T".date("H:i:s.vO")));
				
				if($log = $mysql->prepare("INSERT INTO `logs` VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"))
				{
					$log->execute(array($user['username'], $user['discriminator'], $user['id'], "EDIT_APP_STATE_ACCEPTED", "10", $id, $discord));
				}
				
				$key = array_search($id, $_SESSION['checkingapp']);
				unset($_SESSION['checkingapp'][$key]);
				
				if($upd = $mysql->prepare("UPDATE `apps` SET `deny` = NULL, `isAccepted` = 'true', `addedToWhitelist` = 'false', `denyDate` = ?, `msgSend` = 'false', `isWatched` = 0, `watchExpire` = 0 WHERE `id` = ?"))
				{
					$upd->execute(array($time, $id));
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'warning';
					$_SESSION['error_text'] = 'Aplikacja o ID: '.$id.'<br>['.$discord.']<br><br>Zmienono status na: Akceptowana';
					echo "<script>window.close();</script>";
				}
			}
		}
	}

	if(isset($_POST['editdeny']))
	{
		$id = $_POST['id'];
		$discord = $_POST['discord'];
		$discordid = $_POST['discordid'];
		if(filter_var($id, FILTER_VALIDATE_INT) && filter_var($discordid, FILTER_VALIDATE_INT))
		{
			if($id > 0)
			{
				$time = json_encode(array("\$deny" => date("Y-m-d")."T".date("H:i:s.vO")));
				
				if($log = $mysql->prepare("INSERT INTO `logs` VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"))
				{
					$log->execute(array($user['username'], $user['discriminator'], $user['id'], "EDIT_APP_STATE_DENIED", "20", $id, $discord));
				}
				
				$key = array_search($id, $_SESSION['checkingapp']);
				unset($_SESSION['checkingapp'][$key]);
				
				if($upd = $mysql->prepare("UPDATE `apps` SET `deny` = 'true', `isAccepted` = NULL, `addedToWhitelist` = NULL, `denyDate` = ?, `msgSend` = 'false', `isWatched` = 0, `watchExpire` = 0 WHERE `id` = ?"))
				{
					$upd->execute(array($time, $id));
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'warning';
					$_SESSION['error_text'] = 'Aplikacja o ID: '.$id.'<br>['.$discord.']<br><br>Zmienono status na: Odrzucona';
					echo "<script>window.close();</script>";
				}
			}
		}
	}

	if(isset($_POST['addwl']))
	{
		$id = $_POST['id'];
		$discord = $_POST['discord'];
		$discordid = $_POST['discordid'];
		if(filter_var($id, FILTER_VALIDATE_INT) && filter_var($discordid, FILTER_VALIDATE_INT))
		{
			if($id > 0)
			{
				$time = json_encode(array("\$deny" => date("Y-m-d")."T".date("H:i:s.vO")));
				
				if($log = $mysql->prepare("INSERT INTO `logs` VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"))
				{
					$log->execute(array($user['username'], $user['discriminator'], $user['id'], "SET_APP_STATE_ADDEDTOWL", "50", $id, $discord));
				}
				
				$key = array_search($id, $_SESSION['checkingapp']);
				unset($_SESSION['checkingapp'][$key]);
				
				if($upd = $mysql->prepare("UPDATE `apps` SET `addedToWhitelist` = 'true', `isWatched` = 0, `watchExpire` = 0 WHERE `id` = ?"))
				{
					$upd->execute(array($id));
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'info';
					$_SESSION['error_text'] = 'Aplikacja o ID: '.$id.'<br>['.$discord.']<br><br>Zaznaczono jako dodaną do Whitelisty';
					echo "<script>window.close();</script>";
				}
			}
		}
	}
}
else
{
	unset($_SESSION['checkingapp']);
	unset($_SESSION['memoryarray']);
}

if(isset($_POST['trylogin']))
{
	$user = $_POST['username'];
	$password = $_POST['passwd'];
	if(!empty($user) && !empty($password))
	{
		if($check = $mysql->prepare("SELECT * FROM `users` WHERE `username` = ?"))
		{
			$check->execute(array($user));
			$rows = $check->rowCount();
			if($rows > 0)
			{
				$info = $check->fetchAll();
				if(password_verify($password, $info[0]['password']))
				{
					$_SESSION['login'] = $info[0]['username'];
				}
				else
				{
					$_SESSION['error'] = true;
					$_SESSION['error_type'] = 'error';
					$_SESSION['error_text'] = 'Użytkownik lub hasło są nieprawidłowe!';
				}
			}
			else
			{
				$_SESSION['error'] = true;
				$_SESSION['error_type'] = 'error';
				$_SESSION['error_text'] = 'Użytkownik lub hasło są nieprawidłowe!';
			}
		}
		else
		{
			$_SESSION['error'] = true;
			$_SESSION['error_type'] = 'error';
			$_SESSION['error_text'] = 'Błąd formularza! Spróbuj ponownie!';
		}
	}
	else
	{
		$_SESSION['error'] = true;
		$_SESSION['error_type'] = 'error';
		$_SESSION['error_text'] = 'Nie wszystkie pola zostaly wypełnione!';
	}
}



function toCommunityID($id) {
    if (preg_match('/^STEAM_/', $id)) {
        $parts = explode(':', $id);
        return bcadd(bcadd(bcmul($parts[2], '2'), '76561197960265728'), $parts[1]);
    } elseif (is_numeric($id) && strlen($id) < 16) {
        return bcadd($id, '76561197960265728');
    } else {
        return $id; // We have no idea what this is, so just return it.
    }
}

function toSteamID($id) {
    if (is_numeric($id) && strlen($id) >= 16) {
        $z = bcdiv(bcsub($id, '76561197960265728'), '2');
    } elseif (is_numeric($id)) {
        $z = bcdiv($id, '2'); // Actually new User ID format
    } else {
        return $id; // We have no idea what this is, so just return it.
    }
    $y = bcmod($id, '2');
    return 'STEAM_0:' . $y . ':' . floor($z);
}
// UserID formatting wrappers not included. Ex: RESULT => [U:1:RESULT]
function toUserID($id) {
    if (preg_match('/^STEAM_/', $id)) {
        $split = explode(':', $id);
        return $split[2] * 2 + $split[1];
    } elseif (preg_match('/^765/', $id) && strlen($id) > 15) {
        return bcsub($id, '76561197960265728');
    } else {
        return $id; // We have no idea what this is, so just return it.
    }
}

?>

<head>

	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="bstable/bootstrap-table.css">
	<link rel="stylesheet" href="css/animate.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
	<link href="lib/noty.css" rel="stylesheet">
	<meta charset="utf-8"></meta>

</head>
<?php if(isset($_SESSION['user']))
{
?>
<body>
		<?php if(!isset($_GET['app']) || (isset($_GET['app']) && !filter_var($_GET['app'], FILTER_VALIDATE_INT)))
		{
		?>
		<div class="row" style="margin-top: 6%">
			<div class="col-md-10 col-md-offset-1" id="errorspacer" style="min-height: 50px;">
			</div>
		</div>
		<div class="row" style="margin-top: 2%">
			<div class="col-md-10 col-md-offset-1">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">Aplikacje <i class="fas fa-user pull-right"></i></h3>
					</div>
					<div class="panel-body">
						<a href="?logout=" class="pull-right" style="text-decoration: none">&nbsp;<span class='label label-danger'>Wyloguj się</span></a>
						<ul class="nav nav-tabs" id="myTabs">
							<?php
							if($chap = $mysql->prepare("SELECT * FROM `apps` WHERE `isAccepted` IS NULL AND `deny` IS NULL"))
							{ $chap->execute(); $rows = $chap->rowCount();
							?>
							<li role="navigation" class="active"><a href="#" data-tab="apps">Aplikacje <span class='label label-primary'><?php echo $rows; ?></span></a></li>
							<?php
							}?>
							<?php
							if($chap = $mysql->prepare("SELECT * FROM `apps` WHERE `deny` IS NOT NULL"))
							{ $chap->execute(); $rows = $chap->rowCount();
							?>
							<li role="navigation"><a href="#" data-tab="denied">Odrzucone <span class='label label-danger'><?php echo $rows; ?></span></a></li>
							<?php
							}?>
							<?php
							if($chap = $mysql->prepare("SELECT * FROM `apps` WHERE `isAccepted` IS NOT NULL AND `addedToWhitelist` = 'true'"))
							{ $chap->execute(); $rows = $chap->rowCount();
							?>
							<li role="navigation"><a href="#" data-tab="accepted">Przyjęte <span class='label label-success'><?php echo $rows; ?></span></a></li>
							<?php
							}?>
							<?php
							if($chap = $mysql->prepare("SELECT * FROM `apps` WHERE `isAccepted` IS NOT NULL AND `addedToWhitelist` = 'false'"))
							{ $chap->execute(); $rows = $chap->rowCount();
							?>
							<li role="navigation"><a href="#" data-tab="waiting">Oczekujące na WL <span class='label label-warning'><?php echo $rows; ?></span></a></li>
							<?php
							}?>
							
						</ul>
						
						<div id="apps">
							<table id="apptable" data-toggle="table" data-pagination="true" data-search="true">
								<thead>
									<tr>
										<th data-field="id">#</th>
										<th data-field="discord">Nazwa Discord</th>
										<th data-field="discordid">Discord ID</th>
										<th data-field="steamid">Steam ID</th>
										<th>Akcje</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if($chap = $mysql->prepare("SELECT * FROM `apps`  WHERE `isAccepted` IS NULL AND `deny` IS NULL ORDER BY `createDate` ASC"))
									{
										$chap->execute();
										$rows = $chap->rowCount();
										if($rows > 0)
										{
											$apps = $chap->fetchAll();
											foreach($apps as $app)
											{
									?>
									<tr>
										<td><?php echo $app['id']; ?></td>
										<td><?php echo $app['discord']; ?></td>
										<td><?php echo $app['discordId']; ?></td>
										<td><?php echo $app['steamId']; ?></td>
										<td><form method="post" style="display:inline"><input type="hidden" name="checkapp" value="1" /><input type="hidden" name="appid" value="<?php echo $app['id']; ?>" /><button type="button" class="btn btn-xs btn-info checkapp">Przejrzyj</button></form></td>
									</tr>
										<?php
											}
										}
									}
									?>
								</tbody>
							</table>
						</div>
						<div id="denied" style="display:none">
							<table id="denytable" data-toggle="table" data-pagination="true" data-search="true">
								<thead>
									<tr>
										<th data-field="id">#</th>
										<th data-field="discord">Nazwa Discord</th>
										<th data-field="discordid">Discord ID</th>
										<th data-field="steamid">Steam ID</th>
										<th>Akcje</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if($chap = $mysql->prepare("SELECT * FROM `apps`  WHERE `deny` IS NOT NULL ORDER BY `createDate` DESC"))
									{
										$chap->execute();
										$rows = $chap->rowCount();
										if($rows > 0)
										{
											$apps = $chap->fetchAll();
											foreach($apps as $app)
											{
									?>
									<tr>
										<td><?php echo $app['id']; ?></td>
										<td><?php echo $app['discord']; ?></td>
										<td><?php echo $app['discordId']; ?></td>
										<td><?php echo $app['steamId']; ?></td>
										<td><form method="post" style="display:inline"><input type="hidden" name="checkapp" value="1" /><input type="hidden" name="appid" value="<?php echo $app['id']; ?>" /><button type="button" class="btn btn-xs btn-info checkapp">Przejrzyj</button></form></td>
									</tr>
										<?php
											}
										}
									}
									?>
								</tbody>
							</table>
						</div>
						<div id="accepted" style="display:none">
							<table id="accepttable" data-toggle="table" data-pagination="true" data-search="true">
								<thead>
									<tr>
										<th data-field="id">#</th>
										<th data-field="discord">Nazwa Discord</th>
										<th data-field="discordid">Discord ID</th>
										<th data-field="steamid">Steam ID</th>
										<th>Akcje</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if($chap = $mysql->prepare("SELECT * FROM `apps`  WHERE `isAccepted` IS NOT NULL AND `addedToWhitelist` = 'true' ORDER BY `createDate` DESC"))
									{
										$chap->execute();
										$rows = $chap->rowCount();
										if($rows > 0)
										{
											$apps = $chap->fetchAll();
											foreach($apps as $app)
											{
									?>
									<tr>
										<td><?php echo $app['id']; ?></td>
										<td><?php echo $app['discord']; ?></td>
										<td><?php echo $app['discordId']; ?></td>
										<td><?php echo $app['steamId']; ?></td>
										<td><form method="post" style="display:inline"><input type="hidden" name="checkapp" value="1" /><input type="hidden" name="appid" value="<?php echo $app['id']; ?>" /><button type="button" class="btn btn-xs btn-info checkapp">Przejrzyj</button></form></td>
									</tr>
										<?php
											}
										}
									}
									?>
								</tbody>
							</table>
						</div>
						
						<div id="waiting" style="display:none">
							<table id="wltable" data-toggle="table" data-pagination="true" data-search="true">
								<thead>
									<tr>
										<th data-field="id">#</th>
										<th data-field="discord">Nazwa Discord</th>
										<th data-field="discordid">Discord ID</th>
										<th data-field="steamid">Steam HEX <span class="pull-right">[z czego przekonwertowany (dla potwierdzenia)]</span></th>
										<th>Akcje</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if($chap = $mysql->prepare("SELECT * FROM `apps`  WHERE `isAccepted` IS NOT NULL AND `addedToWhitelist` = 'false' ORDER BY `createDate` DESC"))
									{
										$chap->execute();
										$rows = $chap->rowCount();
										if($rows > 0)
										{
											$apps = $chap->fetchAll();
											foreach($apps as $app)
											{
											?>
									<tr>
										<td><?php echo $app['id']; ?></td>
										<td><?php echo $app['discord']; ?></td>
										<td><?php echo $app['discordId']; ?></td>
										<?php 
										if(strpos($app['steamId'], "STEAM_") !== false) 
										{ 
											$sid = toCommunityID($app['steamId']); 
										} 
										else 
										{ 
											if(filter_var($app['steamId'], FILTER_VALIDATE_INT))
											{
												$sid = $app['steamId'];
											}
											else
											{
												$exp = explode("/", $app['steamId']);
												$found = "NOTFOUND";
												for($i = 0; $i < count($exp); $i++)
												{
													if($exp[$i] == "id")
													{
														$found = $exp[$i+1];
														break;
													}
													
													if($exp[$i] == "profiles")
													{
														$found = $exp[$i+1];
														break;
													}
												}
										
												if($found == "NOTFOUND")
												{
													$ch = curl_init('http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key=460FC9C7F8B5EFEDF792B927692DA56D&vanityurl='.$app['steamId']);
													curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
													$result = json_decode(curl_exec($ch), true);
													if($result['response']['success'] != 42)
													{
														$sid = $result['response']['steamid']; 
													}
													else
													{
														$sid = "BŁĄD PRZY PRZETWARZANIU";
													}
												}
												else
												{
													if(filter_var($app['steamId'], FILTER_VALIDATE_INT))
													{
														$sid = $found; 
													}
													else
													{
														$ch = curl_init('http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key=460FC9C7F8B5EFEDF792B927692DA56D&vanityurl='.$found);
														curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
														$result = json_decode(curl_exec($ch), true);
														if($result['response']['success'] != 42)
														{
															$sid = $result['response']['steamid']; 
														}
														else
														{
															$sid = "BŁĄD PRZY PRZETWARZANIU";
														}
													}
												}
											}
										} 
										
										?>
										<td><?php echo strtoupper(dechex($sid)). "<span class='pull-right'>[".$app['steamId']."]</span>"; ?></td>
										<td><form method="post" style="display:inline"><input type="hidden" name="checkapp" value="1" /><input type="hidden" name="appid" value="<?php echo $app['id']; ?>" /><button type="button" class="btn btn-xs btn-info checkapp">Przejrzyj</button></form></td>
									</tr>
											<?php
											}
										}
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
		}
		else
		{
		?>
		<div class="row" style="margin-top: 6%">
			<div class="col-md-10 col-md-offset-1" id="errorspacer" style="min-height: 50px;">
			</div>
		</div>
		<div class="row" style="margin-top: 2%">
			<div class="col-md-10 col-md-offset-1">
				<form method="post" style="display:inline"><button type="submit" name="gobacktoapps" class="btn btn-block btn-success" style="margin-top: 5px">Zamknij tą aplikację</button></form>
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><b>Przegląd aplikacji o ID: <?php echo $_GET['app']; ?></b><i class="fas fa-user pull-right"></i></h3>
					</div>
					<div class="panel-body">
						<?php
							if($chap = $mysql->prepare("SELECT * FROM `apps` WHERE `id` = ?"))
							{
								$chap->execute(array($_GET['app']));
								$rows = $chap->rowCount();
								if($rows > 0)
								{
									$apps = $chap->fetchAll();
									foreach($apps as $app)
									{
										if($app['discordId'] == null)
										{
											echo "<div class='alert alert-danger'>Wiadomość nie zostanie wysłana automatycznie z powodu braku ID Discord!</div>";
										}
							?>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Discord</b>: <?php echo $app['discord']; ?> [ID: <?php echo $app['discordId']; ?>]</h3>
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>SteamID</b>: <?php echo $app['steamId']; ?></h3>
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Forum</b>: <a href="<?php echo $app['forumProfile']; ?>"><?php echo $app['forumProfile']; ?></a></h3>
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Data urodzenia</b>: <?php echo explode("T", $app['birthday'])[0]; ?></h3>
								</div>

								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym dla Ciebie jest Roleplay?</b></h3>
									<p><?php echo nl2br($app['rp']); ?></p>
								</div>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Podaj swoje doświadczenia z RP i opisz postacie jakie wcześniej odgrywałeś</b></h3>
									<p><?php echo nl2br($app['pastRpCharacter']); ?></p>
								</div>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakie postacie zamierzasz odgrywać na naszym serwerze?</b></h3>
									<p><?php echo nl2br($app['currentRpCharacter']); ?></p>
								</div>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Streamujesz lub nagrywasz content na swoje kanały?<br>Jeśli tak, podaj linki, czy też nagrania z przykładem twojego RP.</b></h3>
									<p><?php echo nl2br($app['stream']); ?></p>
								</div>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Wyślij link do regulaminu oraz wskaż jego dobre i złe strony.</b></h3>
									<p><?php echo nl2br($app['rules']); ?></p>
								</div>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Po co są komendy /me i /do i jak ich używać?</b></h3>
									<p><?php echo nl2br($app['shop']); ?></p>
								</div>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest OOC oraz IC i czym się różnią obie strefy?</b></h3>
									<p><?php echo nl2br($app['car']); ?></p>
								</div>
								
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy używając komendę /do, możesz kłamać?</b></h3>
									<p><?php echo nl2br($app['medo']); ?></p>
								</div>
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Jakim rodzajem czatu jest komenda /tweet i do czego jest używana?</b></h3>
									<p><?php echo nl2br($app['tweet']); ?></p>
								</div>
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czy dozwolone jest zabijanie z zemsty drugiej postaci?</b></h3>
									<p><?php echo nl2br($app['revenge']); ?></p>
								</div>
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest BW? (Brutally Wounded)</b></h3>
									<p><?php echo nl2br($app['bw']); ?></p>
								</div>
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest metagaming? I czy można go używać w grze?</b></h3>
									<p><?php echo nl2br($app['metagaming']); ?></p>
								</div>
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Czym jest powergaming?</b></h3>
									<p><?php echo nl2br($app['powergaming']); ?></p>
								</div>
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Kiedy twoja postać jest zobowiązana zapomnieć o sytuacji która miała miejsce przed po zrespieniu się w szpitalu?</b></h3>
									<p><?php echo nl2br($app['forget']); ?></p>
								</div>
								<div class="well well-sm">
									<h3 style="margin-top: 10px; margin-bottom: 10px;"><b>Co zrobisz gdy dostaniesz crasha, jak powiadomisz o tym administrację?</b></h3>
									<p><?php echo nl2br($app['crash']); ?></p>
								</div>
								
								<div class="row">
									<form method="post">
										<input type="hidden" name="id" value="<?php echo $app['id']; ?>" />
										<input type="hidden" name="discord" value="<?php echo $app['discord']; ?>" />
										<input type="hidden" name="discordid" value="<?php echo $app['discordId']; ?>" />
										<?php if($app['deny'] == null && $app['isAccepted'] == null)
										{
										?>
										<div class="col-md-6">
											<button type="submit" name="acceptapp" class='btn btn-success btn-block'>Akceptuj</button>
										</div>
										<div class="col-md-6">
											<button type="submit" name="denyapp" class='btn btn-danger btn-block'>Odrzuć</button>
										</div>
										<?php 
										} 
										elseif($app['deny'] != null)
										{
										?>
											<div class="col-md-6 col-md-offset-3">
												<button type="submit" name="editaccept" class='btn btn-info btn-block'>Zmień status na akceptowane</button>
											</div>
										<?php
										}
										elseif($app['isAccepted'] != null)
										{
										?>
											<div class="col-md-6<?php echo ($app['addedToWhitelist'] == 'true' ? ' col-md-offset-3' : ''); ?>">
												<button type="submit" name="editdeny" class='btn btn-warning btn-block'>Zmień status na odrzucone</button>
											</div>
											<?php
											if($app['addedToWhitelist'] == 'false')
											{
											?>	
												<div class="col-md-6">
													<button type="submit" name="addwl" class='btn btn-primary btn-block'>Zaznacz jako dodany do WL</button>
												</div>
											<?php
											}
										}
										?>
									</form>
								</div>
							
								<?php
									}
								}
							}
							?>
					</div>
				</div>
				<form method="post" style="display:inline"><button type="submit" name="gobacktoapps" class="btn btn-block btn-success" style="margin-top: 5px">Zamknij tą aplikację</button></form>
			</div>
		</div>
		<?php
		}
	?>
	
	<script src="js/jquery.js"></script>
	<script src="js/bootstrap.min.js"></script>
	
	<script>
		$("#apptable").on("click", '.checkapp', function(e)
		{
			e.preventDefault();

			window.name = "parent";
			
			var form = $(this).closest("form").serialize();
			$.ajax({
				dataType: 'json',
				type: 'post',
				data: form,
				url: 'https://aplikacje.strefarp.pl/ajax.php',
				success: function (received) 
				{
					if(received.status == 1)
					{
						var newWin = window.open('https://aplikacje.strefarp.pl/admin.php?app='+received.id, 'appwindow'+received.id);
						newWin.blur();
					}
					else
					{
						new Noty({
							type: received.type,
							container: '#errorspacer',
							text: received.text,
							timeout: 5500,
							animation: {
								open: 'animated bounceInDown', // Animate.css class names
								close: 'animated bounceOutUp' // Animate.css class names
							}
						}).show();
					}
				},
				error: function () {
					alert("Wystąpił błąd! Odśwież stronę!");
				}
			});
		});
	</script>
	
	<script>
		$("#denytable").on("click", '.checkapp', function(e)
		{
			e.preventDefault();

			window.name = "parent";
			
			var form = $(this).closest("form").serialize();
			$.ajax({
				dataType: 'json',
				type: 'post',
				data: form,
				url: 'https://aplikacje.strefarp.pl/ajax.php',
				success: function (received) 
				{
					if(received.status == 1)
					{
						var newWin = window.open('https://aplikacje.strefarp.pl/admin.php?app='+received.id, 'appwindow'+received.id);
						newWin.blur();
					}
					else
					{
						new Noty({
							type: received.type,
							container: '#errorspacer',
							text: received.text,
							timeout: 5500,
							animation: {
								open: 'animated bounceInDown', // Animate.css class names
								close: 'animated bounceOutUp' // Animate.css class names
							}
						}).show();
					}
				},
				error: function () {
					alert("Wystąpił błąd! Odśwież stronę!");
				}
			});
		});
	</script>
	
	<script>
		$("#accepttable").on("click", '.checkapp', function(e)
		{
			e.preventDefault();

			window.name = "parent";
			
			var form = $(this).closest("form").serialize();
			$.ajax({
				dataType: 'json',
				type: 'post',
				data: form,
				url: 'https://aplikacje.strefarp.pl/ajax.php',
				success: function (received) 
				{
					if(received.status == 1)
					{
						var newWin = window.open('https://aplikacje.strefarp.pl/admin.php?app='+received.id, 'appwindow'+received.id);
						newWin.blur();
					}
					else
					{
						new Noty({
							type: received.type,
							container: '#errorspacer',
							text: received.text,
							timeout: 5500,
							animation: {
								open: 'animated bounceInDown', // Animate.css class names
								close: 'animated bounceOutUp' // Animate.css class names
							}
						}).show();
					}
				},
				error: function () {
					alert("Wystąpił błąd! Odśwież stronę!");
				}
			});
		});
	</script>
	
	<script>
		$("#wltable").on("click", '.checkapp', function(e)
		{
			e.preventDefault();

			window.name = "parent";
			
			var form = $(this).closest("form").serialize();
			$.ajax({
				dataType: 'json',
				type: 'post',
				data: form,
				url: 'https://aplikacje.strefarp.pl/ajax.php',
				success: function (received) 
				{
					if(received.status == 1)
					{
						var newWin = window.open('https://aplikacje.strefarp.pl/admin.php?app='+received.id, 'appwindow'+received.id);
						newWin.blur();
					}
					else
					{
						new Noty({
							type: received.type,
							container: '#errorspacer',
							text: received.text,
							timeout: 5500,
							animation: {
								open: 'animated bounceInDown', // Animate.css class names
								close: 'animated bounceOutUp' // Animate.css class names
							}
						}).show();
					}
				},
				error: function () {
					alert("Wystąpił błąd! Odśwież stronę!");
				}
			});
		});
	</script>
	
	<script src="bstable/bootstrap-table.js"></script>
	<script src="lib/noty.js" type="text/javascript"></script>
	
	<script>
		$('#myTabs a').click(function (e) {
		  e.preventDefault()
		  $(this).tab('show')
		  var data = $(this).data('tab');
		  $("#myTabs a").each(function()
		  {
			var loc = $(this).data('tab');
			$("#"+loc).hide();
		  });
		  $("#"+data).show();
		})
	</script>
	<?php if(isset($_SESSION['error']) && !isset($_GET['app'])) 
	{
	?>
		<script>
		new Noty({
			type: '<?php echo $_SESSION['error_type']; ?>',
			layout: 'centerLeft',
			text: '<?php echo $_SESSION['error_text']; ?>',
			timeout: 7000,
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
<?php
}
else
{
?>
<body>
	<div class="container" style="margin-top: 17%">
		<div class="row">
			<div class="col-md-6 col-md-offset-3" id="errorspacer" style="min-height: 50px;">
			</div>
		</div>
	</div>
	<div class="container" style="margin-top: 2%">
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">Logowanie <i class="fas fa-user pull-right"></i></h3>
					</div>
					<div class="panel-body">
						<?php /*<form method="post" style="display:inline">
							<input type="text" name="username" placeholder="Nazwa użytkownika" class="form-control" required />
							<input type="password" name="passwd" placeholder="Hasło" class="form-control" style="margin-top: 5px;" required />
							<button type="submit" name="trylogin" class="btn btn-block btn-info" style="margin-top: 5px;">Zaloguj się przez discord</button>
						</form>*/ ?>
						<a href="authorize.php?p=a" class="btn btn-lg btn-block btn-info" style="margin-top: 5px;">Zaloguj się przez discord</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="js/jquery.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="lib/noty.js" type="text/javascript"></script>
	
	<?php if(isset($_SESSION['error'])) 
	{
	?>
		<script>
		new Noty({
			type: '<?php echo $_SESSION['error_type']; ?>',
			container: '#errorspacer',
			text: '<?php echo $_SESSION['error_text']; ?>',
			timeout: 3500,
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
<?php
}
?>