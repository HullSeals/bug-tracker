<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Declare Title, Content, Author
$pgAuthor = "";
$pgContent = "";
$useIP = 0; //1 if Yes, 0 if No.

//UserSpice Required
require_once '../users/init.php';  //make sure this path is correct!
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}
?>
        <h3>Welcome to the Hull Seals Support System</h3>
        <p>Please choose from the following options:</p>
        <ul class="list-group list-group-horizontal-lg">
        <a href="create_ticket.php" class="list-group-item list-group-item-dark">Submit Support Ticket</a>
        <a href="tickets.php" class="list-group-item list-group-item-dark">View Current Tickets</a>
        </ul>
      </div>
      <br>
      <?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
