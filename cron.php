<?php

set_time_limit(60);
date_default_timezone_set('UTC');

require "vendor/autoload.php";
require "config.php";
require "downloader/downloader.php";

$downloader = new Downloader();
$downloader->download($config['apiPath']);
