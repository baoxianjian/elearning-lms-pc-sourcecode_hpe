<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/24/15
 * Time: 4:34 PM
 */

namespace components\widgets;


//use yii\grid\GridView;
use kartik\base\Config;
use kartik\grid\GridView;
use kartik\grid\Module;
use Yii;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class TGridView extends GridView{

//    public $layout = "{items}<div style='text-align: left'>{pager}</div>";
//    public $layout = "{items}";
//
    public $export = [];

    public $forceShowAll = 'False';

    public $displayPageSizeSelect = true;

    public $_jsToggleScript;

    public $toggleDataOptions = [
        'all' => [
            'icon' => 'resize-full',
            'label' => '全部数据',
            'class' => 'btn btn-default',
            'title' => '显示全部数据'
        ],
        'page' => [
            'icon' => 'resize-small',
            'label' => '当页数据',
            'class' => 'btn btn-default',
            'title' => '显示首页数据'
        ],
    ];

    public function init()
    {
        $this->export = [
            'label' =>  Yii::t('common','export_button'),
            'options'=>[
                'id'=>'gridview-export-button',
                'title'=>  Yii::t('common','export_button'),
            ]
        ];


        if (Yii::$app->request->getQueryParam('PageShowAll') != null && Yii::$app->request->getQueryParam('PageShowAll') == 'True') {
            $this->forceShowAll = 'True';
        }


        $this->_module = Config::initModule(Module::classname());
        if (empty($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        if (!$this->toggleData) {
            parent::init();
            return;
        }
        $this->_toggleDataKey = $this->options['id'] . '-toggle-data';
        if (isset($_POST[$this->_toggleDataKey])) {
            $this->_isShowAll = $_POST[$this->_toggleDataKey];
        } else {
            $this->_isShowAll = false;
        }
        if ($this->_isShowAll == true || $this->forceShowAll == 'True') {
            $this->dataProvider->pagination = false;
        }
        $this->_jsToggleScript = "kvToggleGridData('{$this->_toggleDataKey}');";
        parent::init();
    }

    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        /* @var $class TLinkPager */
        $pager = $this->pager;
        $pager['id'] = $this->getId();
        $class = ArrayHelper::remove($pager, 'class', TLinkPager::className());
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();

        $pager['displayPageSizeSelect'] = $this->displayPageSizeSelect;

        return $class::widget($pager);
    }

    public $exportConfig = [
        GridView::CSV => ['filename' => 'output-data'],
        GridView::EXCEL => ['filename' => 'output-data'],
//        GridView::TEXT => []
    ];

    protected function renderPanel()
    {
        if (!$this->bootstrap || !is_array($this->panel) || empty($this->panel)) {
            return;
        }
        $type = 'panel-' . ArrayHelper::getValue($this->panel, 'type', 'default');
        $heading = ArrayHelper::getValue($this->panel, 'heading', '');
        $footer = ArrayHelper::getValue($this->panel, 'footer', '');
        $before = ArrayHelper::getValue($this->panel, 'before', '');
        $after = ArrayHelper::getValue($this->panel, 'after', '');
        $headingOptions = ArrayHelper::getValue($this->panel, 'headingOptions', []);
        $footerOptions = ArrayHelper::getValue($this->panel, 'footerOptions', []);
        $beforeOptions = ArrayHelper::getValue($this->panel, 'beforeOptions', []);
        $afterOptions = ArrayHelper::getValue($this->panel, 'afterOptions', []);
        $panelHeading = '';
        $panelBefore = '';
        $panelAfter = '';
        $panelFooter = '';

        $PageShowAllInput =
            Html::hiddenInput('PageShowAll',$this->forceShowAll,array('id'=>'PageShowAll_' . $this->id));

        $defaultPageSize = 10;
        if (isset(Yii::$app->params['defaultPageSize'])) {
            $defaultPageSize = Yii::$app->params['defaultPageSize'];
        }



        if ($this->dataProvider->getPagination() == true) {
            $PageSizeInput =
                Html::hiddenInput('PageSize',$this->dataProvider->getPagination()->pageSize,array('id'=>'PageSize_' . $this->id));
        }
        else
        {
            if (Yii::$app->request->getQueryParam('PageSize') != null) {
                $pageSize = Yii::$app->request->getQueryParam('PageSize');
            }
            else
            {
                $pageSize = $defaultPageSize;
            }

            $PageSizeInput =
                Html::hiddenInput('PageSize', $pageSize, array('id'=>'PageSize_' . $this->id));
        }


        if ($heading !== false) {
            Html::addCssClass($headingOptions, 'panel-heading');
            $content = strtr($this->panelHeadingTemplate, ['{heading}' => $heading]);
            $panelHeading = Html::tag('div', $content, $headingOptions);
        }
        if ($footer !== false) {
            Html::addCssClass($footerOptions, 'panel-footer');
            $content = strtr($this->panelFooterTemplate, ['{footer}' => $footer]);

            $displayFooter = false;
            if ($this->dataProvider->getPagination() == true) {

                if ($this->dataProvider->getPagination()->totalCount > $defaultPageSize)
                {
                    $displayFooter = true;
                }

                if ($this->dataProvider->getPagination()->pageCount > 1 || $displayFooter) {
                    $panelFooter = Html::tag('div', $content, $footerOptions);
                }
            }
        }
        if ($before !== false) {
            Html::addCssClass($beforeOptions, 'kv-panel-before');
            $content = strtr($this->panelBeforeTemplate, ['{before}' => $before]);
            $panelBefore = Html::tag('div', $content, $beforeOptions);
        }
        if ($after !== false) {
            Html::addCssClass($afterOptions, 'kv-panel-after');
            $content = strtr($this->panelAfterTemplate, ['{after}' => $after]);
            $panelAfter = Html::tag('div', $content, $afterOptions);
        }
        $this->layout = strtr(
            $this->panelTemplate,
            [
                '{panelHeading}' => $panelHeading . $PageShowAllInput . $PageSizeInput,
                '{type}' => $type,
                '{panelFooter}' => $panelFooter,
                '{panelBefore}' => $panelBefore,
                '{panelAfter}' => $panelAfter
            ]
        );
    }


}