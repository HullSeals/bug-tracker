<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $db,$user,$us_url_root,$abs_us_root;
$ticSettings = $db->query("SELECT * FROM plg_tickets_settings")->first();
$showTable = false;
if($ticSettings->users_act == 1 && is_numeric($user->data()->id)){
  $us = " AND user = ".$user->data()->id;
  $showTable = true;
}else{
  $us = "";
}

if($ticSettings->agents_act == 1 && is_numeric($user->data()->id)){
  $ag = " AND user = ".$user->data()->id;
  $showTable = true;
}else{
  $ag = "";
}


$q = $db->query("SELECT * FROM plg_tickets WHERE id > 0 $us $ag ORDER BY id DESC LIMIT 5");
$c = $q->count();
if($c > 0 && $showTable){
  $tickets = $q->results();
  ?>
  <p>Your Recent Support Tickets</p>

  <table border="5" cellspacing="2" cellpadding="2" class="table table-dark table-striped table-bordered table-hover">
    <thead>
      <tr>
        <th>Subject</th>
        <th>Status</th>
        <th>
          <?=ucfirst($ticSettings->cat_term)?>
        </th>
        <th>Closed</th>
        <th>Last Updated</th>
        <th>View</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($tickets as $t){ ?>
        <tr>
          <td><?=substr($t->subject,0,50);?></td>
          <td><?=$t->status;?></td>
          <td><?=$t->category;?></td>
          <td><?php bin($t->closed);?></td>
          <td><?=$t->last_updated?></td>
          <td>
            <a href="<?=$us_url_root.$ticSettings->single_view?>?id=<?=$t->id?>" class="btn btn-primary">View</a>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <p><a class="btn btn-primary" href="<?=$us_url_root.$ticSettings->ticket_view?>">View all tickets</a></p>


<?php } ?>
