<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/26/15
 * Time: 9:46 PM
 */
use backend\services\MenuService;

?>

<?php
echo MenuService::getBackendMenu('eln_backend');
?>