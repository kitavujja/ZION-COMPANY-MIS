<?php // KEEP-YOUR-DATA alternative to install.php. Run once in the browser, then delete this file.
require 'config.php';
$d=db();
try{$d->exec('ALTER TABLE users DROP INDEX username');}catch(PDOException $e){}
try{$d->exec('ALTER TABLE users ADD UNIQUE KEY uq_user_role(username,role)');}catch(PDOException $e){}
$h=password_hash('admin123',PASSWORD_DEFAULT);
foreach(['ceo','sales','technician'] as $r){
 $id=n('SELECT id FROM users WHERE role=? ORDER BY id LIMIT 1',[$r]);
 if($id)q('UPDATE users SET username=?,password_hash=?,active=1 WHERE id=?',['admin',$h,$id]);
 else q('INSERT INTO users(username,password_hash,role,full_name) VALUES(?,?,?,?)',['admin',$h,$r,['ceo'=>'Zion CEO','sales'=>'Sales Manager','technician'=>'Technician'][$r]]);
}
echo '<h3>Done.</h3><p>CEO, Sales Manager and Technician now log in with <b>admin</b> / <b>admin123</b>. <a href="index.php">Open portal</a></p>';
