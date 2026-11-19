<?php require 'config.php';need('ceo');
if(post()){try{$a=$_POST['a']??'';
 if($a==='save'){$id=(int)($_POST['id']??0);$f=array_map('trim',[$_POST['username'],$_POST['full_name'],$_POST['contact'],$_POST['email']]);
  if($id){q("UPDATE users SET username=?,full_name=?,contact=?,email=? WHERE id=? AND role='technician'",[...$f,$id]);if($_POST['password']!=='')q("UPDATE users SET password_hash=? WHERE id=? AND role='technician'",[password_hash($_POST['password'],PASSWORD_DEFAULT),$id]);flash('Technician updated.');}
  elseif(strlen($_POST['password'])<6)flash('Password must be at least 6 characters.');
  else{q("INSERT INTO users(username,full_name,contact,email,password_hash,role) VALUES(?,?,?,?,?,'technician')",[...$f,password_hash($_POST['password'],PASSWORD_DEFAULT)]);flash('Technician added.');}}
 if($a==='del'){$id=(int)$_POST['id'];
  if(n("SELECT COUNT(*) FROM orders WHERE technician_id=? AND status IN('Surveyed','Sold')",[$id]))flash('Cannot remove: technician has surveyed or in-progress installations.');
  else{q("UPDATE orders SET status='Pending' WHERE technician_id=? AND status='Assigned'",[$id]);q("DELETE FROM users WHERE id=? AND role='technician'",[$id]);flash('Technician removed.');}}
}catch(PDOException $e){flash($e->getCode()==='23000'?'That username is already taken.':'Error: '.$e->getMessage());}
header('Location: technicians.php');exit;}
$e=isset($_GET['edit'])?q("SELECT * FROM users WHERE id=? AND role='technician'",[(int)$_GET['edit']])->fetch():[];
top('Technicians');?>
<div class="card"><h2><?=$e?'Edit technician':'Add technician'?></h2><form method="post" class="row"><?=tok()?><input type="hidden" name="a" value="save"><input type="hidden" name="id" value="<?=h($e['id']??'')?>">
<input name="full_name" placeholder="Full name" value="<?=h($e['full_name']??'')?>" required><input name="contact" placeholder="Contact" value="<?=h($e['contact']??'')?>"><input name="email" placeholder="Email" value="<?=h($e['email']??'')?>">
<input name="username" placeholder="Username" value="<?=h($e['username']??'')?>" required><input type="password" name="password" placeholder="<?=$e?'New password (blank = keep)':'Password'?>" <?=$e?'':'required'?>><button>Save</button><?php if($e)echo '<a class="btn k" href="technicians.php">Cancel</a>'?></form></div>
<div class="card"><h2>Technicians</h2><table><tr><th>Name</th><th>Username</th><th>Contact</th><th>Email</th><th>Active jobs</th><th></th></tr>
<?php foreach(q("SELECT u.*,(SELECT COUNT(*) FROM orders WHERE technician_id=u.id AND status<>'Completed') j FROM users u WHERE role='technician'")->fetchAll() as $t):?><tr><td><?=h($t['full_name'])?></td><td><?=h($t['username'])?></td><td><?=h($t['contact'])?></td><td><?=h($t['email'])?></td><td><?=$t['j']?></td><td><a href="?edit=<?=$t['id']?>">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm('Remove this technician?')"><?=tok()?><input type="hidden" name="a" value="del"><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn k">Remove</button></form></td></tr><?php endforeach?></table></div>
<?php bottom();
