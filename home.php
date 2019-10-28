<?php

if (!logged_in()) {
    die("<span>You must be logged in!</span>");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$sessUser = getUserByID($user_id);
?>
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script src="homechat.js"></script>
<div class="header-inner">
    <p>Logged in as <?php echo($user_name) ?><a href="logout.php">Logout</a></p>
</div>
<div class="chatRootPanel">
  <div class="channelBar" id="channels" >
  </div>
  <div class="chatPanel">
    <div class="messagePanel" id="messages" channel_id="-1" >
        <div class="textRow"><span class="author">Admin</span><span class="message">Please select a channel from the list on the left.</span></div>
    </div>
    <div class="typePanel">
      <textarea id="msgbox"></textarea>
      <input type="button" id="send" value="Send"/>
    </div>
  </div>
</div>