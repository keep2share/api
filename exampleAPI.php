<?php

include "Keep2ShareAPI.php";

$api = new Keep2ShareAPI();
$api->username='test_api@k2s.cc';
$api->password='testapik2scc';

//getFilesList
var_dump($api->getFilesList('/', 10, 0, ['date_created'=>-1], 'files'));
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
      ["is_folder"]=>
      bool(false)
      ["date_created"]=>
      string(19) "2014-03-31 16:24:40"
      ["size"]=>
      string(4) "2857"
    }
  }
}
*/

//uploadFile
var_dump($api->uploadFile('exampleAPI.php'));
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
