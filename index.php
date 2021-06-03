<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../users/init.php';  //make sure this path is correct!
if (!securePage($_SERVER['PHP_SELF'])){die();}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../assets/includes/headerCenter.php'; ?>
    <meta content="Support" name="description">
    <title>Support | The Hull Seals</title>
</head>
<body>
    <div id="home">
      <?php include '../assets/includes/menuCode.php';?>
        <section class="introduction container">
	    <article id="intro3">
        <h3>Welcome to the Hull Seals Support System</h3>
        <p>Please choose from the following options:</p>
        <ul class="list-group list-group-horizontal-lg">
        <a href="create_ticket.php" class="list-group-item list-group-item-dark">Submit Support Ticket</a>
        <a href="tickets.php" class="list-group-item list-group-item-dark">View Current Tickets</a>
        </ul>
      </div>
      <br>
      </article>
            <div class="clearfix"></div>
        </section>
    </div>
    <?php include '../assets/includes/footer.php'; ?>
</body>
</html>
