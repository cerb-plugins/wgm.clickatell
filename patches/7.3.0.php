<?php
$db = DevblocksPlatform::services()->database();
$logger = DevblocksPlatform::services()->log();
$settings = DevblocksPlatform::services()->pluginSettings();
$tables = $db->metaTables();

$api_id = $settings->get('wgm.clickatell', 'api_id', null);
$api_user = $settings->get('wgm.clickatell', 'api_user', null);
$api_pass = $settings->get('wgm.clickatell', 'api_pass', null);

if(!is_null($api_id) || !is_null($api_user) || !is_null($api_pass)) {
	$api_credentials = [
		'id' => $api_id,
		'user' => $api_user,
		'pass' => $api_pass,
	];
	
	$settings->set('wgm.clickatell', 'credentials', $api_credentials, true, true);
	$settings->delete('wgm.clickatell', ['api_id','api_user','api_pass']);
}

return TRUE;