<?php require 'config.php';
if(!post()){header('Location: index.php');exit;}
$r=$_POST['role']??'';$u=q('SELECT * FROM users WHERE username=? AND role=? AND active=1',[trim($_POST['username']??''),$r])->fetch();
if($u&&password_verify($_POST['password']??'',$u['password_hash'])){session_regenerate_id(true);$_SESSION['u']=['id'=>$u['id'],'username'=>$u['username'],'role'=>$u['role'],'name'=>$u['full_name']];header('Location: dashboard.php');exit;}
header('Location: index.php?e=1&r='.urlencode($r));
