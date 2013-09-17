<?php

include "Keep2ShareAPI.php";

$api = new Keep2ShareAPI();
$api->username='your_email';
$api->password='your_password';

//getFilesList
var_dump($api->getFilesList());
/*
array(3) {
  ["status"]=>
  string(7) "success"
  ["code"]=>
  int(200)
  ["files"]=>
  array(1) {
    [0]=>
    array(4) {
      ["id"]=>
      string(13) "522f0bf5672f8"
      ["name"]=>
      string(9) "README.md"
      ["is_available"]=>
      bool(true)
      ["size"]=>
      string(4) "2857"
    }
  }
}
*/

//uploadFile
var_dump($api->uploadFile('file_path'));
/*
object(stdClass)#2 (3) {
  ["user_file_id"]=>
  string(13) "5238354a724c3"
  ["status"]=>
  string(7) "success"
  ["status_code"]=>
  int(200)
}
*/

?>
