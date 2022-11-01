<?php
$directAccess = 0;
if(count(get_included_files()) ==1){
  require_once "../users/init.php";
  require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
  $directAccess = 1;
}
if(!pluginActive("tickets",true)){ die("Tickets plugin not active");}
if(isset($user) && $user->isLoggedIn()){
$ticstatus = $db->query("SELECT * FROM plg_tickets_status")->first();
$ticcat = $db->query("SELECT * FROM plg_tickets_cats")->first();

$options = array(
  'submit'=>'submit',
  'class'=>'btn btn-primary',
  'value'=>'Create Ticket',
);

if(!empty($_POST['issue'])){
  $response = preProcessForm();
  if($response['form_valid'] == true){
    //let's store some other information
    $response['fields']['user']=$user->data()->id;
    $response['fields']['status']=$ticstatus->status;
    $response['fields']['category']=$ticcat->cat;
    $response['fields']['created']=date("Y-m-d H:i:s");
    $response['fields']['last_updated']=date("Y-m-d H:i:s");
    postProcessForm($response);
    $ticSettings = $db->query("SELECT * FROM plg_tickets_settings")->first();
    if($ticSettings->email_new != ""){
      $to = explode (",", $ticSettings->email_new);
      foreach($to as $t){
        $t = trim($t);
        if (filter_var($t, FILTER_VALIDATE_EMAIL)) {
        $body = $response['fields']['subject']."<br><br>".$response['fields']['issue'];
        email($t,"New Ticket Submitted",$body);
        }
      }
    }
    $lastTicket = $db->query("SELECT id FROM plg_tickets WHERE user = ? ORDER BY ID DESC LIMIT 1",[$user->data()->id])->first();
    $lastTicket = $lastTicket->id;
    $_GET['err'] = "Ticket created";
    $str = http_build_query($_GET, '', '&');
    $cp = $us_url_root.$ticSettings->single_view;
    $redir_url = $cp."?id=".$lastTicket."&".$str;
    $auth = require $abs_us_root.$us_url_root."usersc/plugins/tickets/assets/auth.php";
    $web = $auth["secret"];
    $hook = $auth["key"];
    $ticketsub = $ticket->subject;
    $ticketlink='https://hullseals.space/support/ticket.php?id='.$id;
    //Discord Webhook
    $timestamp = date("c", strtotime("now"));
    $json_data = json_encode([
        "content" => "New Ticket",
        "username" => "HalpyBOT",
        "avatar_url" => "https://hullseals.space/images/emblem_mid.png",
        "tts" => false,
        "embeds" => [
            [
                "title" => "A new ticket has been opened.",
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
                        "value" => html_entity_decode($response['fields']['subject']),
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
    Redirect::to($redir_url);
  }
}
echo "<h3>Welcome to the Hull Seals Support System</h3>
      <p>Please provide a detailed report so we can diagnose issues quickly.</p>
      <p><a href='.' class='btn btn-sm btn-danger' style='float: right;'>Go Back</a></p><br>";

displayForm("plg_tickets",$options);
}//end logged in

if($directAccess ==1){
require $abs_us_root . $us_url_root . 'users/includes/html_footer.php';
}

?>
