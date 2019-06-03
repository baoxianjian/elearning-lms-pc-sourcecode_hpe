<?php


namespace components\widgets;


use Yii;
use yii\helpers\Html;
use yii\widgets\LinkPager;

class TLinkPager extends LinkPager{

    public $prevPageLabel = "<";
    public $nextPageLabel = ">";

    public $firstPageCssClass = "";
    public $lastPageCssClass = "";

    public $firstPageLabel = "|<";
    public $lastPageLabel = ">|";

    public $maxButtonCount = 5;

    public $displayPageSizeSelect = true;

//    public $id = 'default';


    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        $pageSize = $this->pagination->getPageSize();
        $recordCount = $this->pagination->totalCount;

        $defaultPageSize = 10;
        if (isset(Yii::$app->params['defaultPageSize'])) {
            $defaultPageSize = Yii::$app->params['defaultPageSize'];
        }

        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        if ($this->firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        if ($this->lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }



//        $buttons[] = Html::tag('li', Html::tag('span', '共'.$this->pagination->getPageCount().'页/'.$this->pagination->totalCount.'条数据 ',array('style'=>'cursor:default')));

        $buttons[] = Html::tag('li', Html::tag('span', Yii::t('common','all_page_{value}',['value'=>$this->pagination->getPageCount()]),array('style'=>'cursor:default')));
//        style'=>'border:1px solid #717071;width:40px;height:30px;text-align:center'
        $buttons[] =
            Html::textInput('pageNumber','',array('id'=>'pageNumber_' . $this->id,'class'=>'pageNumber', 'onkeyup' =>'this.value=this.value.replace(/\D+/,\'\');', 'onblur'=>'this.value=this.value.replace(/\D+/,\'\');',
                'oninput'=>'ChangePage("'.$this->getId().'","'. Yii::$app->urlManager->hostInfo . $this->pagination->createUrl(1).'",this.value);'));
//            Html::a(Html::button('跳转',array('id'=>'gotoBtn')), "#",array('id'=>'gotoLink',"onclick" => "return gotoPageDirect($('#pageNumber').val()," . $this->pagination->getPageCount() .")"))

        $linkOptions = [];
        $options = [];
//        $options = ["onclick" => "return gotoPageDirect($('#pageNumber').val()," . $this->pagination->getPageCount() .")"];


//        $options = ["onclick" => "$('#jumpPageButton').attr('href','". Yii::$app->urlManager->hostInfo . $this->pagination->createUrl(2)."')"];
//        $buttons[] = Html::tag('li', Html::a('跳转', '#', $linkOptions), $options);

        $buttons[] =  Html::a(Yii::t('common','jump'), '', ['id'=>'jumpPageButton_'. $this->id,'class'=>'jumpPageButton']);

        if ($this->displayPageSizeSelect) {
            $buttons[] =
                Html::DropdownList(
                    'pageSizeSelect',
                    strval($pageSize),
                    [
                        '10' => Yii::t('common','each_page_{value}',['value'=>10]),
                        '50' => Yii::t('common','each_page_{value}',['value'=>50]),
                        '100' => Yii::t('common','each_page_{value}',['value'=>100]),
                        '200' => Yii::t('common','each_page_{value}',['value'=>200])
                    ],
                    array('id' => 'pageSizeSelect_' . $this->id,
                        'class' => 'pageSizeSelect',
                        'onchange' => 'ChangePageSize(this.value);'));
        }
//        $buttons[] =
//            Html::hiddenInput('PageSize',$pageSize,array('id'=>'PageSize_' . $this->id));


//        $buttons[] =
//            Html::textInput('pageNumber','',array('id'=>'pageNumber','style'=>'border:1px solid #717071;width:40px;height:30px;text-align:center')) .
//            Html::a(Html::button('跳转',array('id'=>'gotoBtn')), "#",array('id'=>'gotoLink',"onclick" => "return gotoPageDirect($('#pageNumber').val()," . $this->pagination->getPageCount() .")"));

            //Html::a("跳转", $this->pagination->createUrl(0))

//        onclick="return gotoPageDirect($('#gotoText').val()";



//        return $buttons;
        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }
}