<?php
require_once "../users/init.php";
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("tickets",true)){ die("Tickets plugin not active");}
$id = Input::get('id');

$ticketQ = $db->query("SELECT * FROM plg_tickets WHERE id = ?",[$id]);
$ticketC = $ticketQ->count();
if($ticketC < 1){
  die("Sorry. But this ticket does not exist");
}else{
  $ticket = $ticketQ->first();

}
$stats = $db->query("SELECT * FROM plg_tickets_status")->results();
$cats = $db->query("SELECT * FROM plg_tickets_cats")->results();

if(!isset($user) || !$user->isLoggedIn()){ ?>
  <a href="<?=$us_url_root?>users/login.php">Sorry, you must login.</a>
  <?php
  die;
}
$ticSettings = $db->query("SELECT * FROM plg_tickets_settings")->first();
$notes = $db->query("SELECT * FROM plg_tickets_notes WHERE ticket = ? ORDER BY id DESC",[$id])->results();

$isOwner = false;
if($ticket->user == $user->data()->id){
  $isOwner = true;
}

$isAgent = false;
if($ticket->agent == $user->data()->id){
  $isAgent = true;
}

$canManage = false;
if(hasPerm([$ticSettings->perm_to_assign],$user->data()->id)){
  $canManage = true;
}

if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  if(!empty($_POST['submitComment'])){
    $comment = Input::get("comment");
    if($comment != ""){
      $db->insert("plg_tickets_notes",['user'=>$user->data()->id,"note"=>$comment,"ts"=>date("Y-m-d H:i:s"),'ticket'=>$id]);
      //If someone other than the user updated the ticket, email the user
      if($user->data()->id != $ticket->user && $ticSettings->email_user == 1){
        $fetchQ = $db->query("SELECT id,email FROM users WHERE id = ?",[$ticket->user]);
        $fetchC = $fetchQ->count();
        if($fetchC > 0){
          $fetch = $fetchQ->first();
          email($fetch->email,"Your ticket has been updated",$comment);
        }
      }

      if($user->data()->id != $ticket->agent && $ticSettings->email_agent == 1 && $ticket->agent != 0){
        $fetchQ = $db->query("SELECT id,email FROM users WHERE id = ?",[$ticket->agent]);
        $fetchC = $fetchQ->count();
        if($fetchC > 0){
          $fetch = $fetchQ->first();
          email($fetch->email,"One of your tickets has a new comment",$comment);
        }
      }

      $auth = require $abs_us_root.$us_url_root."usersc/plugins/tickets/assets/auth.php";
      $web = $auth["secret"];
      $hook = $auth["key"];
      $ticketsub = $ticket->subject;
      $ticketlink='https://hullseals.space/support/ticket.php?id='.$id;
      //Discord Webhook
      $timestamp = date("c", strtotime("now"));
      $json_data = json_encode([
          "content" => "Ticket has New Comment",
          "username" => "HalpyBOT",
          "avatar_url" => "https://hullseals.space/images/emblem_mid.png",
          "tts" => false,
          "embeds" => [
              [
                  "title" => "Ticket has New Comment",
                  "type" => "rich",
                  "timestamp" => $timestamp,
                  "color" => hexdec( "F5921F" ),
                  "footer" => [
                      "text" => "Hull Seals Ticket Notification System",
                      "icon_url" => "https://hullseals.space/images/emblem_mid.png"
                  ],
                  "fields" => [
                      [
                          "name" => "Ticket",
                          "value" => html_entity_decode($ticketsub),
                          "inline" => true
                      ],
                      [
                          "name" => "Link",
                          "value" => $ticketlink,
                          "inline" => true
                      ]

                  ]
              ]
          ]

      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
      $ch = curl_init( 'https://discord.com/api/webhooks/'.$web.'/'.$hook);
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
      curl_setopt( $ch, CURLOPT_POST, 1);
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt( $ch, CURLOPT_HEADER, 0);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
      $response = curl_exec( $ch );
      curl_close( $ch );

      $db->update("plg_tickets",$id,["last_updated"=>date("Y-m-d H:i:s")]);
      Redirect::to("ticket.php?id=$id&err=Note added");
    }
  }

  if(!empty($_POST['saveSettings'] && ($isAgent || $canManage))){
    $fields = [
      'closed'=>Input::get('closed'),
      'status'=>Input::get('status'),
      'category'=>Input::get('category'),
    ];
    $db->update("plg_tickets",$id,$fields);
    logger($user->data()->id,"Tickets","Updated Ticket $id ".json_encode($fields));
    Redirect::to("ticket.php?id=$id&err=Ticket Updated");
  }
}

?>
<link href="<?=$us_url_root?>usersc/plugins/tickets/assets/style.css" rel="stylesheet">
<div class="row" style="color:black">
  <div class="col-12 col-sm-8 offset-sm-2">
    <p><a href="./create_ticket.php" class="btn btn-sm btn-primary" style="float:left;">Submit a Ticket</a> <a href="./tickets.php" class="btn btn-sm btn-danger" style="float: right;">Go Back</a></p><br>
    <br>
    <?php if($isAgent || $canManage){ ?>
      <div class="card">
        <h3 class="text-center">Manage Ticket</h3>
        <div class="row" style="padding:10px;">
          <div class="col-12 col-sm-4">
            <form class="" action="" method="post">
              <input type="hidden" value="<?=Token::generate();?>" name="csrf">
              <div class="form-group">
                <label for="">Open/Close Ticket</label>
                <select class="form-control" name="closed">
                  <option value="0" <?php if($ticket->closed == 0){echo "selected='selected'";} ?>>Open</option>
                  <option value="1" <?php if($ticket->closed == 1){echo "selected='selected'";} ?>>Closed</option>
                </select>
              </div>

            </div>

            <div class="col-12 col-sm-4">
              <div class="form-group">
                <label for="">Set <?=ucfirst($ticSettings->cat_term);?></label>
                <select class="form-control" name="category">
                  <?php
                  $found = false;
                  foreach($cats as $c){ ?>
                    <option value="<?=$c->cat?>"
                      <?php if($c->cat == $ticket->category){
                        echo "selected = 'selected'";
                        $found = true;
                      }
                      ?>
                      ><?=ucfirst($c->cat);?></option>
                      <?php
                    }
                    if(!$found){ ?>
                      <option value="<?=$ticket->category?>"><?=ucfirst($ticket->category);?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-12 col-sm-4">
                <div class="form-group">
                  <label for="">Set Status</label>
                  <select class="form-control" name="status">
                    <?php
                    $found = false;
                    foreach($stats as $c){ ?>
                      <option value="<?=$c->status?>"
                        <?php if($c->status == $ticket->status){
                          echo "selected = 'selected'";
                          $found = true;
                        }
                        ?>
                        ><?=ucfirst($c->status);?></option>
                        <?php
                      }
                      if(!$found){ ?>
                        <option value="<?=$ticket->status?>"><?=ucfirst($ticket->status);?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row text-right" style="padding:10px;">
                <div class="col-12 text-right">
                  <input type="submit" name="saveSettings" value="Save Settings" class="btn btn-primary">
                </form>
              </div>
            </div>
          </div>
          <br>
        <?php } ?>

        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-12 col-sm-4">
                <h3>Ticket #<?=$id?></h3>
              </div>
              <div class="col-12 col-sm-4 text-center">
                Submitted By: <?=echouser($ticket->user);?><br>
                <?php if($ticket->agent > 0){
                  echo ucfirst($ticSettings->agent_term).": ";
                  echouser($ticket->agent);
                }
                ?>
              </div>
              <div class="col-12 col-sm-4 text-right">
                Created: <?=$ticket->created?><br>
                Updated: <?=$ticket->last_updated?>
              </div>
            </div>
            <div class="row">
              <div class="col-6">
                Ticket Closed: <?=bin($ticket->closed);?>
              </div>
              <div class="col-6 text-right">
                Ticket Status: <b style="color:blue;"><?=$ticket->status?></b>
              </div>
            </div>

          </div>
          <div class="card-body">
            <?php
            $form = $db->query("SELECT * FROM plg_tickets_form WHERE col_type != ?",["hidden"])->results();
            foreach($form as $f){
              $col = $f->col;
              ?>
              <h5 class="card-title"><b><?=$f->table_descrip?></b></h5>
              <p class="card-text" style="white-space: pre-line;"><?=$ticket->$col?></p>

            <?php } ?>
          </div>
        </div>

        <br>
        <p style="color:white;">Leave a Comment</a>
        <form class="" action="" method="post">
          <input type="hidden" value="<?=Token::generate();?>" name="csrf">
          <textarea name="comment" rows="4" class="form-control"></textarea><br>
          <input type="submit" class='btn btn-success btn-sm' name="submitComment" value="Submit Comment">
        </form>

        <?php
        if(count($notes) > 0){ ?>
          <br>
          <p style="color:white;">Previous Comments</a>
          <?php
          foreach($notes as $c){ ?>
            <div class="row">
              <div class="col-12">
                <br>
                <div class="card">
                  <div class="card-header">
                    <div class="row">
                      <div class="col-6">
                        <a href="#"><strong><?php echouser($c->user);?></strong></a>
                      </div>
                      <div class="col-6 text-right">
                        <?php echo time2str($c->ts);?>
                      </div>
                    </div>
                    <div class="card-body">
                      <b><?=$c->note?></b>

                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php }
        }
        ?>
      </div>
    </div>
    <?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
