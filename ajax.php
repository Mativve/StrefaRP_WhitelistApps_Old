<?php

session_start();

if(isset($_SESSION['user']))
{
	$users = array(
	array("NickDiscord",					"4 cyferki po #",				LongID discorda),
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
		die (json_encode(array("status" => -1)));
	}
	
	DEFINE("mysql_host", "localhost");
	DEFINE("mysql_user", "root");
	DEFINE("mysql_database", "apps");
	DEFINE("mysql_pass", "");


	$mysql = new PDO('mysql:host='.mysql_host.';dbname='.mysql_database, mysql_user, mysql_pass);

	$mysql->query('SET NAMES utf8');
	$mysql->query('SET CHARACTER SET utf8');
	$mysql->query('SET collation_connection = utf8_polish_ci');

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
						$errarr = array("status" => 1, "id" => $id);
						echo json_encode($errarr);
					}
				}
				else
				{
					$errarr = array("status" => -1, "type" => "warning", "text" => "Ktoś już przegląda aplikacje o ID: ".$id." [".$appinfo[0]['discord']."]!<br>Zacznij przeglądać inną tak aby nie nakładać się na siebie!");
					echo json_encode($errarr);
				}
			}
		}
	}
	else
	{
		header("Location: https://aplikacje.strefarp.pl/");
	}
	
}
else
{
	header("Location: https://aplikacje.strefarp.pl/");
}
?>