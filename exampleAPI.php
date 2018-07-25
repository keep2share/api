<?php

include "Keep2ShareAPI.php";

$api = new Keep2ShareAPI();
$api->access_token = 'f6a4a627860a4a1cd0223db141f4e76ead1583af';

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

// Upload file
var_dump($api->uploadFile('PATH-TO-LOCAL-FILE'));
/*
 * array (size=5)
  'status' => string 'success' (length=7)
  'success' => boolean true
  'status_code' => int 200
  'user_file_id' => string 'cd4540513fe4d' (length=13)
  'link' => string 'https://k2s.cc/file/cd4540513fe4d' (length=33)
 */
