<?
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
?>
