<?php
$uri = $_SERVER['REQUEST_URI'];
$uri = substr($uri, 8);
$encodeduri = htmlspecialchars($uri);
echo '<html><body>';
echo 'The bambots tools have been moved to a new server.<br />';
echo "The new server can be found at <a href='https://bambots.brucemyers.com$uri'>https://bambots.brucemyers.com$encodeduri</a>";
echo '</body></html>';
