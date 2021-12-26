<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Declare Title, Content, Author
$pgAuthor = "";
$pgContent = "";
$useIP = 0; //1 if Yes, 0 if No.
$activePage = ''; //Used only for Menu Bar Sites

//If you have any custom scripts, CSS, etc, you MUST declare them here.
//They will be inserted at the bottom of the <head> section.
$customContent = '<!-- Your Content Here -->';

//UserSpice Required
require_once '../users/init.php';  //make sure this path is correct!
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}if (!securePage($_SERVER['PHP_SELF'])){die();}
if (isset($_GET['err'])) {
  Redirect::to('index.php');
}
?>
        <h3>Welcome to the Hull Seals Support System</h3>
        <p>Please provide a detailed report so we can diagnose issues quickly.</p>
        <p><a href="." class="btn btn-sm btn-danger" style="float: right;">Go Back</a></p><br>
        <?php include $abs_us_root.$us_url_root."usersc/plugins/tickets/create_ticket.php"; ?>
      </div>
      <br>
      <?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
