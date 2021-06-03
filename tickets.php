<?php
require_once "../users/init.php";
if(!pluginActive("tickets",true)){ die("Tickets plugin not active");}
if (!securePage($_SERVER['PHP_SELF'])){die();}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../assets/includes/headerCenter.php'; ?>
    <meta content="Support Tickets" name="description">
    <title>Support Tickets | The Hull Seals</title>
</head>
<body>
    <div id="home">
      <?php include '../assets/includes/menuCode.php';?>
      <section class="introduction container">
    <article id="intro3">
<p><a href="." class="btn btn-sm btn-danger" style="float: right;">Go Back</a></p><br>
<?php
$ticSettings = $db->query("SELECT * FROM plg_tickets_settings")->first();
$closed = Input::get('closed');
$filter = Input::get('filter');
if(!hasPerm([$ticSettings->perm_to_assign],$user->data()->id)){
  $me = true;
}else{
  $me = false;
}


if($closed != "true"){
  $cl = " AND closed = 0";
}else{
  $cl = "";
}

if(is_numeric($filter)){
  $fi = " AND category = ".$filter;
}else{
  $fi = "";
}

if($me){
  $mi = " AND agent = ".$user->data()->id." OR agent = 0";
}else{
  $mi = "";
}

if(!is_numeric(Input::get('limit'))){
  $limit = 500;
}else{
  $limit = Input::get('limit');
}

$ticketsQ = $db->query("SELECT * FROM plg_tickets WHERE id > 0 $cl $fi $mi ORDER BY id DESC LIMIT $limit ");
$ticketsC = $ticketsQ->count();
$tickets = $ticketsQ->results();


$assignable = $db->query("SELECT * FROM user_permission_matches WHERE permission_id = ?",[$ticSettings->perm])->results();
$agents = [];
foreach($assignable as $k=>$v){
  $q = $db->query("SELECT id,fname,lname FROM users WHERE id = ?",[$v->user_id]);
  $c = $q->count();
  if($c < 1){
    continue;
  }else{
    $f = $q->first();
    $agents[$f->id]['uid'] = $f->id;
    $agents[$f->id]['fname'] = $f->fname;
    $agents[$f->id]['lname'] = $f->lname;
  }
}

array_multisort(array_map(function($element) {
  return $element['fname'];
}, $agents), SORT_ASC, $agents);


if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }


  if(!empty($_POST['change_agent'])){
    if(hasPerm([$ticSettings->perm_to_assign],$user->data()->id)){
      $db->update("plg_tickets",Input::get('changeThis'),['agent'=>Input::get('new_agent'),"last_updated"=>date("Y-m-d H:i:s")]);
      Redirect::to("tickets.php?err=Agent+changed&closed=$closed&filter=$filter&limit=$limit");
    }else{
      logger($user->data()->id,"Ticket Error","Tried to illegally change agent on ticket");
    }
  }
}

?>
<div class="row">
  <div class="col-12">
    <h2 class="text-center">Tickets (<?=$ticketsC?>)</h2>
    <p class="text-center">
      <?php
      $cp = currentPage();
      if($closed == ""){ ?>
        <a class="btn btn-sm btn-info" href="<?=$cp?>?closed=true">Show Closed Tickets</a>
      <?php }else{ ?>
        <a class="btn btn-sm btn-info" href="<?=$cp?>">Hide Closed Tickets</a>
      <?php } ?>
    </p>
    <table class="table table-hover table-dark table-bordered table-striped" style="color:white">
      <thead>
        <tr>
          <th>User</th>
          <th>Subject</th>
          <th>Status</th>
          <th>
            <?=ucfirst($ticSettings->cat_term)?>
          </th>
          <?php if($cl == ""){ ?>
            <th>Closed</th>
          <?php } ?>
          <th>Created</th>
          <th>Last Updated</th>
          <th>View</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($tickets as $t){ ?>
          <tr>
            <td><?php echouser($t->user);?></td>
            <td><?=substr($t->subject,0,100);?></td>
            <td><?=$t->status;?></td>
            <td><?=$t->category;?></td>
            <?php if($cl == ""){ ?>
              <td><?php bin($t->closed);?></td>
            <?php } ?>
            <td><?=$t->created?></td>
            <td><?=$t->last_updated?></td>
            <td>
              <a href="<?=$us_url_root.$ticSettings->single_view?>?id=<?=$t->id?>" class="btn btn-primary">View</a>
            </td>

          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<br><a class="btn btn-primary btn-lg" href="https://hullseals.space/usersc/plugins/tickets/create_ticket.php" role="button">Submit a Support Ticket</a><br>
</div>
</article>
      <div class="clearfix"></div>
  </section>
</div>
   <?php include '../assets/includes/footer.php'; ?>
</body>
</html>

<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>
$(document).ready(function () {
  $('.paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
});
</script>
