<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}
if (isset($_GET['err'])) {
  Redirect::to('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../assets/includes/headerCenter.php'; ?>
    <meta content="Ticket Submission" name="description">
    <title>Support | The Hull Seals</title>
</head>
<body>
    <div id="home">
      <?php include '../assets/includes/menuCode.php';?>
        <section class="introduction container">
	    <article id="intro3">
        <h3>Welcome to the Hull Seals Support System</h3>
        <p>Please provide a detailed report so we can diagnose issues quickly.</p>
        <p><a href="." class="btn btn-sm btn-danger" style="float: right;">Go Back</a></p><br>
        <?php include $abs_us_root.$us_url_root."usersc/plugins/tickets/create_ticket.php"; ?>
      </div>
      <br>
      </article>
            <div class="clearfix"></div>
        </section>
    </div>
    <?php include '../assets/includes/footer.php'; ?>
</body>
</html>
