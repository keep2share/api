<?php

include "Keep2ShareAPI.php";

$api = new Keep2ShareAPI('YOUR-LOGIN', 'YOUR-PASSWORD');
//$api->verbose = true;
echo 'Your file: ' . $api->autoUploader('PATH-TO-LOCAL-FILE');
