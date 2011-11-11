<?php
/**
 * Simple PHP view
 */
// http header
foreach ($response->headers as $header) {
    //header($header);
    echo "$header\n";
}
// http body
$body = (object)$response->body;
?><html>
    <body><?php echo $body->greeting?></body>
</html>
