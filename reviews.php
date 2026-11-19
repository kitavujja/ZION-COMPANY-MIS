<?php require 'config.php';need('technician','ceo');top(role()==='ceo'?'Customer Reviews':'Reviews of My Work');
$w=role()==='technician'?' WHERE o.technician_id='.(int)me()['id']:'';
$rows=q('SELECT r.*,o.service_type,o.site_location,c.full_name cname,t.full_name tname FROM reviews r JOIN orders o ON o.id=r.order_id JOIN users c ON c.id=r.customer_id LEFT JOIN users t ON t.id=o.technician_id'.$w.' ORDER BY r.id DESC')->fetchAll();
echo '<div class="card"><h2>Average rating: '.($rows?round(array_sum(array_column($rows,'rating'))/count($rows),1).' / 5':'no reviews yet').'</h2><table><tr><th>Date</th><th>Customer</th><th>Job</th><th>Technician</th><th>Rating</th><th>Comment</th></tr>';
foreach($rows as $r)echo '<tr><td>'.substr($r['created_at'],0,10).'</td><td>'.h($r['cname']).'</td><td>#'.$r['order_id'].' '.h($r['service_type']).', '.h($r['site_location']).'</td><td>'.h($r['tname']?:'-').'</td><td>'.str_repeat('★',$r['rating']).str_repeat('☆',5-$r['rating']).'</td><td>'.h($r['comment']).'</td></tr>';
echo '</table></div>';bottom();
