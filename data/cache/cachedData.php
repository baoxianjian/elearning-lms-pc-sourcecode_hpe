<?php
$cachedConfigs=[];
$cachedSites=[];

require(__DIR__ . '/cachedConfigs.php');
require(__DIR__ . '/../../common/config/sites-local.php');

return [
	'cachedConfigs' => $cachedConfigs,
	'cachedSites' => $cachedSites,
];
