<?php
$rootDir = __DIR__ . '/../..';
require_once($rootDir . '/common/eLearningLMS.php');

//require_once($rootDir . '/components/log4php/Logger.php');
//Logger::configure($rootDir . '/common/config/log4php.xml');

require_once($rootDir . '/common/base/BaseActiveRecord.php');
require_once($rootDir . '/common/base/BaseActiveRecordMongoDB.php');
require_once($rootDir . '/common/base/BaseController.php');
require_once($rootDir . '/common/base/BaseModel.php');
require_once($rootDir . '/common/base/BaseView.php');
require_once($rootDir . '/common/base/BaseAction.php');
require_once($rootDir . '/common/base/BaseModule.php');
require_once($rootDir . '/common/base/BaseFilter.php');
//require_once($rootDir . '/common/base/AuthManager.php');

require_once($rootDir . '/components/widgets/BaseWidget.php');
require_once($rootDir . '/components/widgets/InhritLayout.php');
require_once($rootDir . '/components/widgets/Alert.php');
require_once($rootDir . '/components/widgets/LoopData.php');
require_once($rootDir . '/components/widgets/Breadcrumbs.php');
require_once($rootDir . '/components/widgets/TKindEditor.php');
require_once($rootDir . '/components/widgets/ActiveForm.php');
require_once($rootDir . '/components/widgets/ActiveField.php');
require_once($rootDir . '/components/widgets/Tabs.php');
//require_once($rootDir . '/components/widgets/TBaseWidget.php');
//require_once($rootDir . '/components/widgets/TLoop.php');
require_once($rootDir . '/components/widgets/TLinkPager.php');
require_once($rootDir . '/components/widgets/TGridView.php');
require_once($rootDir . '/components/widgets/TDatePicker.php');
require_once($rootDir . '/components/widgets/TJsTree.php');
require_once($rootDir . '/components/widgets/TJsTreeAsset.php');
require_once($rootDir . '/components/widgets/TModal.php');


require_once($rootDir . '/common/helpers/TBaseHelper.php');
require_once($rootDir . '/common/helpers/TStringHelper.php');
require_once($rootDir . '/common/helpers/TTimeHelper.php');
require_once($rootDir . '/common/helpers/TFileHelper.php');
require_once($rootDir . '/common/helpers/TSessionHelper.php');
require_once($rootDir . '/common/helpers/TLoggerHelper.php');
require_once($rootDir . '/common/helpers/TURLHelper.php');
require_once($rootDir . '/common/helpers/TClientHelper.php');
require_once($rootDir . '/common/helpers/TMessageHelper.php');

require_once($rootDir . '/common/crypt/MessageCrypt.php');
require_once($rootDir . '/components/phpqrcode/phpqrcode.php');
require_once($rootDir . '/components/mpdf60/mpdf.php');
require_once($rootDir . '/common/crypt/WechatMsgCrypt.php');
//require_once($rootDir . '/components/menu/BackendFrameMenu.php');




/*
require_once($rootDir . '/base/helpers/TStringHelper.php');
require_once($rootDir . '/base/widgets/TBaseWidget.php');
require_once($rootDir . '/base/widgets/TLoop.php');
require_once($rootDir . '/base/helpers/TFileHelper.php');


require_once($rootDir . '/data/cache/cachedData.php');
*/
