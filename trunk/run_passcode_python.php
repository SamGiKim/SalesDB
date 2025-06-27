<?php
header("Content-Type: text/plain");

$output = shell_exec("python3 ./passcode.py 2>&1");
echo $output;
?>