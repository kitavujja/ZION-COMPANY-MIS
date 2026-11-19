<?php require 'config.php';need('sales','ceo','customer');$id=(int)($_GET['order']??0);
$o=q('SELECT o.id,o.site_location,o.service_type,c.full_name,c.contact,s.products_total,s.survey_fee,s.install_fee,s.total,s.sale_date FROM orders o JOIN users c ON c.id=o.customer_id JOIN sales s ON s.order_id=o.id WHERE o.id=?'.(role()==='customer'?' AND o.customer_id='.(int)me()['id']:''),[$id])->fetch();
if(!$o)exit('Receipt not available');
top('Receipt ZGE-'.str_pad($o['id'],5,'0',STR_PAD_LEFT));
echo '<div class="card"><h2>Zion Global Engineering Company</h2><p>Customer: <b>'.h($o['full_name']).'</b> · '.h($o['contact']).'<br>Site: '.h($o['site_location']).' ('.h($o['service_type']).')<br>Date: '.h($o['sale_date']).'</p><table><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Amount</th></tr>';
foreach(q('SELECT p.name,i.qty,i.unit_price FROM order_items i JOIN products p ON p.id=i.product_id WHERE i.order_id=?',[$id])->fetchAll() as $i)echo '<tr><td>'.h($i['name']).'</td><td>'.$i['qty'].'</td><td>'.ugx($i['unit_price']).'</td><td>'.ugx($i['qty']*$i['unit_price']).'</td></tr>';
echo '<tr><td colspan="3"><b>Products subtotal</b></td><td>'.ugx($o['products_total']).'</td></tr><tr><td colspan="3">Survey fee</td><td>'.ugx($o['survey_fee']).'</td></tr><tr><td colspan="3">Installation fee</td><td>'.ugx($o['install_fee']).'</td></tr><tr><td colspan="3"><b>TOTAL</b></td><td><b>'.ugx($o['total']).'</b></td></tr></table><p class="noprint"><button onclick="print()">Print receipt</button> <a class="btn k" href="orders.php">Back</a></p></div>';
bottom();
