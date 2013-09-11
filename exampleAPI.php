<?php

include "Keep2ShareAPI.php";

$api = new Keep2ShareAPI();
$api->username='test_api@k2s.cc';
$api->password='testapik2scc';

//getUploadFormData
var_dump($api->getUploadFormData());
//get File List
var_dump($api->getFilesList());

?>