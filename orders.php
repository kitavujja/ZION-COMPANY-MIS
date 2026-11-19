<?php require 'config.php';need();$u=me();$R=role();
if(post()){$a=$_POST['a']??'';$oid=(int)($_POST['oid']??0);
try{
if($a==='new'&&$R==='customer'){$items=array_filter($_POST['qty']??[],fn($v)=>(int)$v>0);
 if(!$items||trim($_POST['site_location']??'')==='')flash('Enter a site location and select at least one product.');
 else{db()->beginTransaction();
  q('INSERT INTO orders(customer_id,service_type,site_location,description) VALUES(?,?,?,?)',[$u['id'],$_POST['service_type']==='Maintenance'?'Maintenance':'Installation',trim($_POST['site_location']),trim($_POST['description'])]);$id=db()->lastInsertId();
  foreach($items as $pid=>$qt)q('INSERT INTO order_items(order_id,product_id,qty,unit_price) SELECT ?,id,?,unit_price FROM products WHERE id=?',[$id,(int)$qt,(int)$pid]);
  db()->commit();flash('Order #'.$id.' placed.');}}
if($a==='review'&&$R==='customer'&&q("SELECT id FROM orders WHERE id=? AND customer_id=? AND status='Completed' AND id NOT IN(SELECT order_id FROM reviews)",[$oid,$u['id']])->fetch()){q('INSERT INTO reviews(order_id,customer_id,rating,comment) VALUES(?,?,?,?)',[$oid,$u['id'],max(1,min(5,(int)$_POST['rating'])),trim($_POST['comment'])]);flash('Thank you for your review.');}
if($a==='assign'&&$R==='sales'){$t=(int)$_POST['technician_id'];q("UPDATE orders SET technician_id=?,status='Assigned' WHERE id=? AND status IN('Pending','Assigned') AND ? IN(SELECT id FROM users WHERE role='technician' AND active=1)",[$t,$oid,$t]);flash('Technician assigned for survey.');}
if($a==='survey'&&$R==='technician'){q("UPDATE orders SET status='Surveyed',survey_notes=? WHERE id=? AND technician_id=? AND status='Assigned'",[trim($_POST['notes']),$oid,$u['id']]);flash('Survey confirmed. Sales manager can now make the sale.');}
if($a==='done'&&$R==='technician'){q("UPDATE orders SET status='Completed' WHERE id=? AND technician_id=? AND status='Sold'",[$oid,$u['id']]);flash('Installation marked as done.');}
if($a==='sale'&&$R==='sales'&&q("SELECT id FROM orders WHERE id=? AND status='Surveyed'",[$oid])->fetch()){
 $it=q('SELECT i.product_id,i.qty,i.unit_price,p.stock_qty,p.name FROM order_items i JOIN products p ON p.id=i.product_id WHERE i.order_id=?',[$oid])->fetchAll();
 $short=array_filter($it,fn($r)=>$r['qty']>$r['stock_qty']);
 if($short)flash('Insufficient stock for: '.implode(', ',array_column($short,'name')).'. Record a purchase first.');
 else{$pt=array_sum(array_map(fn($r)=>$r['qty']*$r['unit_price'],$it));$sf=max(0,(float)$_POST['survey_fee']);$if=max(0,(float)$_POST['install_fee']);
  db()->beginTransaction();
  foreach($it as $r)q('UPDATE products SET stock_qty=stock_qty-? WHERE id=?',[$r['qty'],$r['product_id']]);
  q('INSERT INTO sales(order_id,products_total,survey_fee,install_fee,total) VALUES(?,?,?,?,?)',[$oid,$pt,$sf,$if,$pt+$sf+$if]);
  q("UPDATE orders SET status='Sold' WHERE id=?",[$oid]);db()->commit();header('Location: receipt.php?order='.$oid);exit;}}
}catch(PDOException $e){if(db()->inTransaction())db()->rollBack();flash('Error: '.$e->getMessage());}
header('Location: orders.php');exit;}
function actions($o,$R,$techs){$s=$o['status'];$id=(int)$o['id'];$f='<form method="post" class="row">'.tok().'<input type="hidden" name="oid" value="'.$id.'">';$out='';
 if($R==='sales'){
  if($s==='Pending'||$s==='Assigned'){$out=$f.'<input type="hidden" name="a" value="assign"><select name="technician_id" required><option value="">Technician...</option>';foreach($techs as $t)$out.='<option value="'.$t['id'].'"'.($t['id']==$o['technician_id']?' selected':'').'>'.h($t['full_name']).'</option>';$out.='</select><button>'.($s==='Pending'?'Assign':'Reassign').'</button></form>';}
  elseif($s==='Surveyed')$out=$f.'<input type="hidden" name="a" value="sale"><input type="number" name="survey_fee" placeholder="Survey fee" required min="0" style="width:110px"><input type="number" name="install_fee" placeholder="Install fee" required min="0" style="width:110px"><button>Make sale</button></form>';}
 if($R==='technician'){
  if($s==='Assigned')$out=$f.'<input type="hidden" name="a" value="survey"><input name="notes" placeholder="Survey notes" required><button>Confirm survey</button></form>';
  elseif($s==='Sold')$out=$f.'<input type="hidden" name="a" value="done"><button>Mark installation done</button></form>';
  elseif($s==='Surveyed')$out='Awaiting sale ';}
 if(in_array($s,['Sold','Completed']))$out.=' <a class="btn k" href="receipt.php?order='.$id.'" target="_blank">Receipt</a>';
 if($R==='customer'&&$s==='Completed')$out.=$o['rv']?'<br>'.str_repeat('★',$o['rv']).str_repeat('☆',5-$o['rv']).' '.h($o['rc']):$f.'<input type="hidden" name="a" value="review"><select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select><input name="comment" placeholder="Your review" required><button>Review</button></form>';
 return $out;}
top(['customer'=>'My Orders','technician'=>'My Jobs'][$R]??'Orders');
if($R==='customer'){$loc=n('SELECT location FROM users WHERE id=?',[$u['id']]);
 echo '<div class="card"><h2>Place a new order</h2><form method="post">'.tok().'<input type="hidden" name="a" value="new"><div class="row"><select name="service_type"><option>Installation</option><option>Maintenance</option></select><input name="site_location" placeholder="Site location" value="'.h($loc).'" required size="30"></div><p><textarea name="description" rows="2" placeholder="Describe the work needed" style="width:100%"></textarea></p><table><tr><th>Product</th><th>Price</th><th>In stock</th><th>Qty</th></tr>';
 foreach(q('SELECT * FROM products WHERE stock_qty>0 ORDER BY name')->fetchAll() as $p)echo '<tr><td>'.h($p['name']).'</td><td>'.ugx($p['unit_price']).'</td><td>'.$p['stock_qty'].'</td><td><input type="number" min="0" max="'.$p['stock_qty'].'" name="qty['.$p['id'].']" value="0" style="width:80px"></td></tr>';
 echo '</table><p><button>Submit order</button></p></form></div>';}
$w=['customer'=>' WHERE o.customer_id='.(int)$u['id'],'technician'=>' WHERE o.technician_id='.(int)$u['id']][$R]??'';
$techs=$R==='sales'?q("SELECT id,full_name FROM users WHERE role='technician' AND active=1")->fetchAll():[];
echo '<div class="card"><h2>'.($R==='customer'?'My orders':($R==='technician'?'Assigned jobs':'All orders')).'</h2><table><tr><th>#</th><th>Date</th>'.($R!=='customer'?'<th>Customer</th>':'').'<th>Service / site</th><th>Products</th><th>Status</th>'.($R!=='technician'?'<th>Technician</th>':'').'<th>Total</th><th>Action</th></tr>';
foreach(q(baseq().$w.' ORDER BY o.id DESC')->fetchAll() as $o)echo '<tr><td>'.$o['id'].'</td><td>'.substr($o['created_at'],0,10).'</td>'.($R!=='customer'?'<td>'.h($o['cname']).'<br><small>'.h($o['ccontact']).'</small></td>':'').'<td>'.h($o['service_type']).'<br>'.h($o['site_location']).($o['survey_notes']?'<br><small>Survey: '.h($o['survey_notes']).'</small>':'').'</td><td>'.h($o['items']).'</td><td>'.badge($o['status']).'</td>'.($R!=='technician'?'<td>'.h($o['tname']?:'-').'</td>':'').'<td>'.($o['total']!==null?ugx($o['total']):'-').'</td><td>'.actions($o,$R,$techs).'</td></tr>';
echo '</table></div>';bottom();
