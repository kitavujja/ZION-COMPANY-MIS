<?php
session_name('ZION_MIS_SID');session_start();
function db(){static $p;return $p??=new PDO('mysql:host=127.0.0.1;dbname=zion_mis_db;charset=utf8mb4','root','',
[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}
function q($s,$a=[]){$x=db()->prepare($s);$x->execute($a);return $x;}
function n($s,$a=[]){return q($s,$a)->fetchColumn();}
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function me(){$u=$_SESSION['u']??null;return(is_array($u)&&isset($u['id'],$u['name'],$u['role'])&&in_array($u['role'],['customer','sales','technician','ceo'],true))?$u:null;}
function role(){return me()['role']??'';}
function need(...$r){if(!me()){header('Location: index.php');exit;}if($r&&!in_array(role(),$r,true)){http_response_code(403);exit('Access denied');}}
function csrf(){return $_SESSION['t']??=bin2hex(random_bytes(16));}
function tok(){return '<input type="hidden" name="t" value="'.csrf().'">';}
function post(){if($_SERVER['REQUEST_METHOD']!=='POST')return false;if(!hash_equals(csrf(),$_POST['t']??''))exit('Invalid token');return true;}
function flash($m){$_SESSION['f']=$m;}
function msg(){$m=$_SESSION['f']??'';unset($_SESSION['f']);return $m?'<p class="ok">'.h($m).'</p>':'';}
function ugx($v){return 'UGX '.number_format((float)$v);}
function badge($s){return '<span class="b b-'.strtolower($s).'">'.h($s).'</span>';}
function fin(){$s=(float)n('SELECT COALESCE(SUM(total),0) FROM sales');$p=(float)n('SELECT COALESCE(SUM(qty*unit_cost),0) FROM purchases');$e=(float)n
('SELECT COALESCE(SUM(amount),0) FROM expenses');return ['sales'=>$s,'purchases'=>$p,'expenses'=>$e,'spend'=>$p+$e,'profit'=>$s-$p-$e];}
function baseq(){return 'SELECT o.*,c.full_name cname,c.contact ccontact,t.full_name tname,s.total,
(SELECT GROUP_CONCAT(CONCAT(p.name,\' x\',i.qty) SEPARATOR \', \') FROM order_items i JOIN products p ON p.id=i.product_id WHERE i.order_id=o.id) 
items,(SELECT rating FROM reviews WHERE order_id=o.id) rv,(SELECT comment FROM reviews WHERE order_id=o.id) rc FROM orders o JOIN users c ON c.id=o.customer_id 
LEFT JOIN users t ON t.id=o.technician_id LEFT JOIN sales s ON s.order_id=o.id';}
function top($title){
 $L=['customer'=>'Customer','sales'=>'Sales Manager','technician'=>'Technician','ceo'=>'CEO'];
 $nav=[['dashboard.php','Dashboard','customer,sales,technician,ceo'],['orders.php',['customer'=>'My Orders','technician'=>
 'My Jobs'][role()]??'Orders','customer,sales,technician,ceo'],['inventory.php','Inventory & Finance','sales,ceo'],['technicians.php','Technicians','ceo'],
 ['reviews.php','Reviews','technician,ceo']];
 echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' 
 · Zion MIS</title><link rel="stylesheet" href="style.css"></head><body><input type="checkbox" id="mt" hidden><div class="app"><aside><div class="brand">
 ZION<span>GLOBAL ENGINEERING</span></div><nav>';
 foreach($nav as $x)if(in_array(role(),explode(',',$x[2])))echo '<a href="'.$x[0].'"'.(basename($_SERVER['SCRIPT_NAME'])===$x[0]?' class="on"':'').'>'.$x[1].'</a>';
 echo '</nav><div class="who">'.h(me()['name']).'<br>'.($L[role()]??'').'<br><a href="logout.php">Sign out</a></div></aside>
 <main><label for="mt" class="burger">☰ Menu</label><h1>'.h($title).'</h1>'.msg();
}
function bottom(){echo '</main></div></body></html>';}
