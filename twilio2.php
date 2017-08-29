<?php
include 'Harvey_Bot.php';
$bot = new Harvey_Bot();
$resp = $bot->main($_POST['Body']);
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Message><?php echo $resp; ?></Message>
</Response>