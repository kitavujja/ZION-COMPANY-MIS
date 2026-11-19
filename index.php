<?php require 'config.php';if(me()){header('Location: dashboard.php');exit;}
$open=in_array($_GET['r']??'',['customer','sales','technician','ceo'])?$_GET['r']:'customer';
$T=['customer'=>'Customer','sales'=>'Sales Manager','technician'=>'Technician','ceo'=>'CEO'];?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Zion MIS Portal</title><link rel="stylesheet" href="style.css"></head><body>
<div class="portal"><h1>ZION</h1><p style="text-align:center;margin-top:0">Global Engineering Company<br><small>Fire-protection products and installation</small></p><?=msg()?>
<div class="tabs"><?php foreach($T as $k=>$v)echo "<button type='button' data-r='$k' onclick=\"tab('$k')\">$v</button>"?></div>
<?php foreach($T as $k=>$v):?><div class="panel" data-r="<?=$k?>"><form method="post" action="login.php" class="login"><h2><?=$v?> Login</h2>
<?php if(($_GET['e']??'')&&$open===$k)echo '<p class="err">Invalid username or password.</p>'?><?=tok()?><input type="hidden" name="role" value="<?=$k?>">
<input name="username" placeholder="Username" required><input type="password" name="password" placeholder="Password" required><button style="width:100%">Sign in</button>
<?php if($k==='customer')echo '<p>New customer? <a href="signup.php"><b>Sign up here</b></a></p>'?></form></div><?php endforeach?></div>
<script>function tab(r){document.querySelectorAll('.panel,.tabs button').forEach(e=>e.classList.toggle('on',e.dataset.r===r))}tab('<?=$open?>')</script></body></html>
