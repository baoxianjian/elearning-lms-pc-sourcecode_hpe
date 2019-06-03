<?php



namespace components\widgets;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;


class TJsTree extends InputWidget
{
    /**
     * @var array Data configuration.
     * If left as false the HTML inside the jstree container element is used to populate the tree (that should be an unordered list with list items).
     */
    public $data = [];

    public $name = 'js_tree';

//    public $id = 'tree';

    /**
     * @var array Stores all defaults for the core
     */
    public $core = [
        'expand_selected_onload' => true,
        'themes' => [
            'icons' => false
        ]
    ];

    /**
     * @var array Stores all defaults for the checkbox plugin
     */
    public $checkbox = [
        'three_state' => true,
        'keep_selected_style' => false];

    /**
     * @var array Stores all defaults for the contextmenu plugin
     */
    public $contextmenu = [];

    /**
     * @var array Stores all defaults for the drag'n'drop plugin
     */
    public $dnd = [];

    /**
     * @var array Stores all defaults for the search plugin
     */
    public $search = [];

    /**
     * @var string the settings function used to sort the nodes.
     * It is executed in the tree's context, accepts two nodes as arguments and should return `1` or `-1`.
     */
    public $sort = [];

    /**
     * @var array Stores all defaults for the state plugin
     */
    public $state = [];

    /**
     * @var array Configure which plugins will be active on an instance. Should be an array of strings, where each element is a plugin name.
     *
     * ['types', 'dnd', 'contextmenu', 'wholerow', 'state', 'checkbox']
     */
    public $plugins = ['types', 'state'];

    /**
     * @var array Stores all defaults for the types plugin
     */
    public $types = [
        '#' => [
            'max_children' => '0',
//            'max_depth' => '4',
            'valid_children' => ['root']
        ],
        'default' => [
            'icon' => '/static/common/images/tree/default.png',
            'valid_children' => ['file']
        ],
        'file' => [
            'icon' => 'glyphicon glyphicon-file',
            'valid_children' => 'none'
        ],
        'root' => [
            'icon' => '/static/common/images/tree/root.png',
            'valid_children' => 'none'
        ]
    ];

    public $selectNodeAction = null;

    public $treeFlag = null;

    public $needRegister = 'True';

//    public $selectedNodeId = null;

//    public $treeType = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $suffix = '';
        if (isset($this->treeFlag) && $this->treeFlag != null && $this->treeFlag != '') {
            $suffix = '_' . $this->treeFlag;
        }
        $this->registerAssets();

        if (!$this->hasModel()) {
            echo Html::hiddenInput($this->options['id'].$suffix, null, [ 'id' => $this->options['id'].$suffix ]);
        }
        else {
            echo Html::activeTextInput($this->model, $this->attribute, ['class' => 'hidden', 'value' => $this->value]);
            Html::addCssClass($this->options, "js_tree_{$this->attribute}");
        }

        $this->options['id'] = 'jsTree_' . $this->options['id'] . $suffix;
        
        echo Html::tag('div', '', $this->options);
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();

        if (isset($this->needRegister)  && $this->needRegister != null && $this->needRegister != 'False') {
            TJsTreeAsset::register($view);
        }

        $config = [
            'core' => array_merge(['data' => $this->data], $this->core),
            'checkbox' => $this->checkbox,
            'contextmenu' => $this->contextmenu,
            'dnd' => $this->dnd,
            'search' => $this->search,
            'sort' => $this->sort,
            'state' => $this->state,
            'plugins' => $this->plugins,
            'types' => $this->types
        ];
        $defaults = Json::encode($config);

        //$inputId = (!$this->hasModel()) ? $this->options['id'] : Html::getInputId($this->model, $this->attribute);

        $selectNodeAction = $this->selectNodeAction;
//        $treeType = $this->treeType;

        $suffix = '';
        if (isset($this->treeFlag) && $this->treeFlag != null && $this->treeFlag != '') {
            $suffix = '_' . $this->treeFlag;
        }

        if ($selectNodeAction != null) {
            $js = <<<SCRIPT

;(function($, window, document, undefined) {
    $('#jsTree_{$this->options['id']}{$suffix}')
         .bind("load_node.jstree", function (event, data) {
            $('#jsTree_{$this->options['id']}_loaded_result{$suffix}').val(data.node.id);
        })
         .bind("model.jstree", function (event, data) {
            var newNodes = JSON.stringify(data.nodes);
            var oldNodes = $('#jsTree_{$this->options['id']}_displayed_result{$suffix}').val();

            var finalNodes = '';

            if (oldNodes == undefined || oldNodes == '')
            {
                finalNodes = newNodes;
            }
            else
            {
                finalNodes = oldNodes.substring(0,oldNodes.length-1) + ',' + newNodes.substring(1,newNodes.length);
            }


            $('#jsTree_{$this->options['id']}_displayed_result{$suffix}').val(finalNodes);
        })
        .bind("changed.jstree", function(event, data){
            $('#jsTree_{$this->options['id']}_changed_result{$suffix}').val(JSON.stringify(data.selected));
        })
        .bind("select_node.jstree", function (event, data) {
            var oldid = $('#jsTree_{$this->options['id']}_selected_result{$suffix}').val();
        	$('#jsTree_{$this->options['id']}_selected_result{$suffix}').val(JSON.stringify(data.selected));
            var newid = $('#jsTree_{$this->options['id']}_selected_result{$suffix}').val();

            if (oldid != newid)
            {
                var treeNodeKid=data.instance.get_node(data.selected[0]).id;
                var treeType=data.instance.get_node(data.selected[0]).original.attr.tree_type;
                var ajaxUrl = "{$selectNodeAction}";
                ajaxUrl = urlreplace(ajaxUrl,'TreeNodeKid',treeNodeKid);
                ajaxUrl = urlreplace(ajaxUrl,'TreeType',treeType);
                ajaxGet(ajaxUrl, "rightList");
            }
		})
        .jstree({$defaults});
        if (typeof TreeCallback == 'function'){
            TreeCallback();
        }
})(window.jQuery, window, document);
SCRIPT;

        }
        else
        {
            $js = <<<SCRIPT
;(function($, window, document, undefined) {
    $('#jsTree_{$this->options['id']}{$suffix}')
        .bind("load_node.jstree", function (event, data) {
            $('#jsTree_{$this->options['id']}_loaded_result{$suffix}').val(data.node.id);
        })
        .bind("model.jstree", function (event, data) {
           var newNodes = JSON.stringify(data.nodes);
            var oldNodes = $('#jsTree_{$this->options['id']}_displayed_result{$suffix}').val();

            var finalNodes = '';

            if (oldNodes == undefined || oldNodes == '')
            {
                finalNodes = newNodes;
            }
            else
            {
                finalNodes = oldNodes.substring(0,oldNodes.length-1) + ',' + newNodes.substring(1,newNodes.length);
            }


            $('#jsTree_{$this->options['id']}_displayed_result{$suffix}').val(finalNodes);
        })
        .bind("changed.jstree", function(event, data){
            $('#jsTree_{$this->options['id']}_changed_result{$suffix}').val(JSON.stringify(data.selected));
        })
        .bind("select_node.jstree", function (event, data) {
        	$('#jsTree_{$this->options['id']}_selected_result{$suffix}').val(JSON.stringify(data.selected));

        	if (!$("#"+data.node.id).hasClass('jstree-open')){
        	    $('#jsTree_{$this->options['id']}{$suffix}').jstree("open_node", "#"+data.node.id);
        	}
		})
        .jstree({$defaults});
        if (typeof TreeCallback == 'function'){
            TreeCallback();
        }
})(window.jQuery, window, document);
SCRIPT;
        }
        $view->registerJs($js);
    }
}
