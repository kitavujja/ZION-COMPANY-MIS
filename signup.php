<?php require 'config.php';
if(post()){$f=array_map('trim',[$_POST['username'],$_POST['full_name'],$_POST['contact'],$_POST['location']]);
 if(in_array('',$f,true)||strlen($_POST['password'])<6)$e='Fill in all fields; password needs at least 6 characters.';
 else try{q("INSERT INTO users(username,password_hash,role,full_name,contact,location) VALUES(?,?,'customer',?,?,?)",[$f[0],password_hash($_POST['password'],PASSWORD_DEFAULT),$f[1],$f[2],$f[3]]);flash('Account created. Please log in.');header('Location: index.php?r=customer');exit;}
 catch(PDOException $x){$e='That username is already taken.';}}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Customer Sign up</title><link rel="stylesheet" href="style.css"></head><body>
<form method="post" class="login"><h2>Customer Sign up</h2><?php if(!empty($e))echo '<p class="err">'.h($e).'</p>';echo tok()?>
<input name="full_name" placeholder="Full name" required><input name="contact" placeholder="Contact (phone)" required><input name="location" placeholder="Location" required>
<input name="username" placeholder="Choose a username" required><input type="password" name="password" placeholder="Choose a password (6+ chars)" required>
<button style="width:100%">Create account</button><p><a href="index.php">&larr; Back to portal</a></p></form></body></html>
