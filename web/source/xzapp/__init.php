<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$account_api = WeAccount::createByUniacid();
if ($action == 'post-step') {
	define('FRAME', '');
} else {
	define('FRAME', $account_api->menuFrame);
}