<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<title>Password Change</title>
	<meta charset="utf-8"/>
	<meta name="robots" content="noindex">
	<script src="/bower_components/bootstrap/assets/js/jquery.js" type="text/javascript"></script>
	<script src="/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
	<link href="/bower_components/bootstrap/dist/css/bootstrap.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="container">

<?php

/*
# Title: ActiveDirectory-Password-Change 

# File name: index.php
# Description: Change your AcitveDirectory Password.
# Tags: activedirectory, change, user, password, php, website
# Project: 

# Author: Janik von Rotz
# Author Contact: http://janikvonrotz.ch

# Create Date: 2013-09-23
# Last Edit Date: 2013-09-23
# Version: 1.0.0

*/

session_start(); 

$SecKey = "khtDP5R46zF5a1oUA5fzIGBGMWvnfEEqbJ7OZBFYo2YE0pvLZrwu5zSsSz2uHsX";

if($_GET['sec'] == $SecKey){

	$ldapuser = "CN=changeADpwd,OU=serviceaccounts,OU=users,DC=yourdomain,DC=com";
	$ldappwd = "KiPLk=WlBlRt2zWC";

	function create_ldap_connection($binddn,$password){
	
		$ldaphost = "ldaps://adserver.yourdomain.com/";
		$port = 636;
		$ldap_conn = ldap_connect($ldaphost, $port) or die("<div class='alert alert-danger'>Sorry! Could not connect to LDAP server ($ldaphost)</div>");
		ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

		if($ldap_conn){
			$username = "changeADpwd";
			$result = ldap_bind($ldap_conn,$binddn,$password) or die("<div class='alert alert-danger'>Error: Couldn't bind to server using provided credentials! -$ldap_conn-$binddn</div>");
		}

		if($result){
			return $ldap_conn;
		}else{
			die("<div class='alert alert-danger'>Error: Couldn't bind to server with supplied credentials!</div>");
		}
	}

	function get_user_dn( $ldap_conn, $user_name ) {
	
		/* Write the below details as per your AD setting */
		$basedn = "OU=users,DC=yourdimaon,DC=com";
		
		/* Search the user details in AD server */
		$searchResults = ldap_search( $ldap_conn, $basedn, $user_name );		
		if(!is_resource($searchResults)){
			die('Error in search results.');
		}
	
		/* Get the first entry from the searched result */
		$entry = ldap_first_entry( $ldap_conn, $searchResults );
		return ldap_get_dn( $ldap_conn, $entry );
	}

	function pwd_encryption($newPassword){
	
		$newPassword = "\"" . $newPassword . "\"";
		$len = strlen( $newPassword );
		$newPassw = "";
		
		for($i = 0; $i < $len; $i++ ){
			$newPassw .= "{$newPassword{$i}}\000";
		}
		
		$userdata["unicodePwd"] = $newPassw;
		return $userdata;
	}

	if (isset($_POST['send'])){
				
		if(empty($_POST['pw']) OR empty($_POST['pwnew']) OR empty($_POST['name'])) {
				$error = "<div class='alert alert-danger'>Die Passwortfelder und der Benutzername m&uuml;ssen ausgef&uuml;llt sein.</div>";						
		} 
		if($_POST['pwnew'] == $_POST['pwnew2']) {
							
			if(!preg_match('/^.{8,12}$/', $_POST['pwnew'])) {
				$error .= "<div class='alert alert-danger'>Das Passwort muss zwischen 8-12 Zeichen enthalten.</div>";
			}
			if(!preg_match('/^((?=.*[a-zäöü])(?=.*[A-ZÄÖÜ])).*$/', $_POST['pwnew'])) {
				$error .= "<div class='alert alert-danger'>Das Passwort muss aus Gross und Kleinbuchstaben bestehen. (a-z, A-Z)</div>";
			}
			if(!preg_match('/^.*\d/',$_POST['pwnew'])) {
				$error .= "<div class='alert alert-danger'>Das Passwort muss mindestens eine Zahl enthalten (0-9)</div>";
			}
			if(!preg_match('/.*\W+/', $_POST['pwnew'])) {
				$error .= "<div class='alert alert-danger'>Das Passwort muss ein Sonderzeichen enthalten (Bps. ''% $ * + ( ) ='', etc.)</div>";
			}								
			
			if(empty($error)){
			
				$user_name = "(userPrincipalName=".$_POST['name'].")";					
				$ldap_conn = create_ldap_connection($ldapuser,$ldappwd);
				
				if($userDn = get_user_dn($ldap_conn,$user_name)){
					
					if($ldap_checkpw = ldap_bind($ldap_conn,$userDn,$_POST['pw'])){
					
						$userdata = pwd_encryption($_POST['pwnew']);
						$ldap_resetpw = create_ldap_connection($ldapuser,$ldappwd);							
						ldap_bind($ldapuser,$ldappwd);
						
						if($result = ldap_mod_replace($ldap_resetpw, $userDn , $userdata)){
							
							session_destroy();
							die("<div class='alert alert-success'>Das Passwort wurde erfolgreich ge&auml;ndert.</div>");
							
						}else{
							
							$error = "<div class='alert alert-danger'>Systemfehler. Das Passwort konnte nicht ge&auml;ndert werden.</div>";
						}
					}else{
					
						$error = "<div class='alert alert-danger'>Das alte Passwort stimmt nicht und Sie k&ouml;nnen nicht authentifiziert werden! Bitte geben Sie das alte Passwort nochmals ein.</div>";
						?>
						<script type="text/javascript">
							$(function () {
								$("#pw").focus();
							});
						</script>
						<?
					}
				}else{
					$error = "<div class='alert alert-danger'>Der Benutzer ist leider unbekannt.</div>";
					?>
					<script type="text/javascript">
					 $(function () {
						$("#name").focus();
					 });
					</script>
					<?		
				}
			}			
		}else{
		
			$error = "<div class='alert alert-danger'>Die neuen Passw&ouml;rter stimmen nicht &uuml;berrein!</div>";
			$_POST['pwnew'] = "";
			$_POST['pwnew2'] = "";
			?>
			<script type="text/javascript">
			$(function () {
				$("#pwnew").focus();
			});
			</script>
			<?
		}
	}

	?>		
		
	<h2>Password Change</h2>
	
	<div id="errorfield"><?php echo "$error" ?></div>
	
	<form class="form-horizontal" role="form" method="post" action="index-New.php?sec=<? echo $SecKey; ?>">	
	
		<div class="form-group">
			<label class="col-lg-2 control-label" for="name">Benutzername</label>
			<div class="col-lg-10">
				<input class="form-control" name="name" type="text" value="<?php
				if(isset($_POST['name'])){
					echo $_POST['name']; 
				}
				?>" id="name" placeholder="vorname.nachname@vbl.ch">
			</div>
		</div>		
		
		<div class="form-group">
			<label class="col-lg-2 control-label" for="pw">altes Passwort</label>
			<div class="col-lg-10">
				<input class="form-control" name="pw" type="password" value="<?=$_POST['pw']?>" id="pw">
			</div>
		</div>	
			
		<div class="form-group">
			<label class="col-lg-2 control-label" for="pwnew">neues Passwort</label>
			<div class="col-lg-10">
				<input class="form-control" name="pwnew" type="password" value="<?=$_POST['pwnew']?>" id="pwnew">
			</div>
		</div>	
		
		<div class="form-group">
			<label class="col-lg-2 control-label" for="pwnew2">nochmal neues Passwort</label>
			<div class="col-lg-10">
				<input class="form-control" name="pwnew2" type="password" value="<?=$_POST['pwnew2']?>">
			</div>
		</div>	
				
		<div class="form-group">
			<div class="col-lg-10">
				<button name="send" type="submit" class="btn">neues Passwort abschicken</button>
			</div>
		</div>
		
	</form>	

	<?php 
	
}

?>

</div>
</body>
</html>