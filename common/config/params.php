<?php
$params = [
    'adminEmail' => 'service@hpe-online.com',
    'supportEmail' => 'service@hpe-online.com',
    'user.passwordResetTokenExpire' => 3600, //一小时
    'defaultPageSize' => 10,
    'fileSecretKey' => 'elearning-hpe',
    'fileSecretExpire' => 300,//文件防盗参数有效期
];

$params = array_merge(
    $params,
    require(__DIR__ . '/../../data/cache/cachedData.php')
);

return $params;
