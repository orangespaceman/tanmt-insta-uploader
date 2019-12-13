<?php

set_time_limit(60);
date_default_timezone_set('UTC');

require "vendor/autoload.php";
require "config.php";
require "uploader/uploader.php";

$uploader = new Uploader();
$image = $uploader->download($config['apiPath']);

if ($image) {
  $uploader->upload(
    $image['imagePath'],
    $image['title'],
    $image['tags'],
    $config['username'],
    $config['password']);
}

