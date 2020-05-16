
<!-- Main Page content - can be empty in this case -->
<div id="page">
  <?php include "partials/askforname.php"; ?>
  <?php include "partials/cookiebanner.php"; ?>
  <?php include "partials/tutoverlay.php"; ?>
</div>


<!-- Different tabs as menu elements -->
<nav id="menu">
  <div class="page" id="settings">
    <?php include "settings.php"; ?>
  </div>

  <div id="dashboard">
    <?php include "dashboard.php"; ?>
  </div>

  <div class="page" id="messages">
    <?php include "messages.php"; ?>
  </div>

  <div class="page" id="chat-window">
    <?php include "chat_window.php"; ?>
  </div>

</nav>
