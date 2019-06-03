<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\framework\FwUser */

?>
<div class="eln-user-view">


    <?php

    $tabItems = [
        [
            'label' => Yii::t('backend','tab_basic_info'),
            'content' =>  DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                        'user_name',
                        'real_name',
                        'nick_name',
                        'user_no',
                        [
                            'label' => Yii::t('backend','gender'),
                            'value' => $model->getGenderName(),
                        ],
                        'email:email',
                        'birthday',
                        'mobile_no',
                        'home_phone_no',
                        'telephone_no',
                        [
                            'label' => Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','company')]),
                            'value' => $model->getCompanyName(),
                        ],
                        [
                            'label' => Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','orgnization')]),
                            'value' => $model->getOrgnizationName(),
                        ],
                        [
                            'label' => Yii::t('backend','relate_{value}',['value'=>Yii::t('backend','domain')]),
                            'value' => $model->getDomainName(),
                        ],
                        [
                            'label' => Yii::t('backend','reporting_manager'),
                            'value' => $model->getReportingManagerName(),
                        ],
                        [
                            'label' => Yii::t('backend', 'manager_flag'),
                            'value' => $model->getManagerFlagText(),
                        ],
                        [
                            'label' => Yii::t('backend', 'status'),
                            'value' => $model->getStatusText(),
                        ],
                        [
                            'label' => Yii::t('backend','employee_status'),
                            'value' => $model->getEmployeeStatusName(),
                        ],
                        'onboard_day',
                        'rank',
                        [
                            'label' => Yii::t('backend','work_place'),
                            'value' => $model->getWorkPlaceName(),
                        ],
                        [
                            'label' => Yii::t('backend','position_mgr_level'),
                            'value' => $model->getPositionMgrLevelName(),
                        ],
                        'description:text',
                        [
                            'label' => Yii::t('backend','valid_start_at'),
                            'value' => $model->getValidStartAtName(),
                        ],
                        [
                            'label' => Yii::t('backend','valid_end_at'),
                            'value' => $model->getValidEndAtName(),
                        ],
                        'sequence_number'
                    ]
                ]),
            'options' => ['id' => 'base-tab-view'],
        ],
        [
            'label' => Yii::t('backend','tab_position_info'),
//            'content' =>  '',
            'options' => ['id' => 'position-tab-view'],
        ],
        [
            'label' => Yii::t('backend','tab_other_info'),
            'content' =>  DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => Yii::t('backend', 'language'),
                        'value' => $model->getLanguageName(),
                    ],
                    [
                        'label' => Yii::t('backend', 'system_theme'),
                        'value' => $model->getThemeName(),
                    ],
                    [
                        'label' => Yii::t('backend', 'timezone'),
                        'value' => $model->getTimezoneName(),
                    ],
                    [
                        'label' => Yii::t('backend', 'location'),
                        'value' => $model->getLocationName(),
                    ],
//                    'auth_token',
//                    'additional_accounts',
                    'failed_login_times',
                    'failed_login_start_at:datetime',
                    'failed_login_last_at:datetime',
                    'find_pwd_req_at:datetime',
                    'last_pwd_change_at:datetime',
                    [
                        'label' => Yii::t('backend', 'last_pwd_change_reason'),
                        'value' => $model->getLastPwdChangeReasonName(),
                    ],
                    'last_login_at:datetime',
                    'last_login_ip',
                    'last_login_mac',
                    'last_action_at:datetime',
                    'login_number'
                ]
            ]),
            'options' => ['id' => 'other-tab-view'],
        ],

    ];

    $url = Url::toRoute(['user-position/view','userId'=>$model->kid]);

    ?>

    <script>
        TabClear('position-tab-view');
        //            alert($('#other-tab-create').html());
        <?php if (isset($url) && $url != null):?>
        var ajaxUrl = "<?=$url?>";
        //        alert(ajaxUrl);
        TabLoad('position-tab-view', ajaxUrl);
        <?php endif?>
    </script>
    <?=
    Tabs::widget([
        'id' => 'tabs',
        'items' => $tabItems,
//        'options' => ['tag' => 'div'],
//        'itemOptions' => ['tag' => 'div'],
//        'headerOptions' => ['class' => 'my-class'],
    ]);
    ?>

</div>
