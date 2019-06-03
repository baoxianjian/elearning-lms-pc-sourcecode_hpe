<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace components\widgets;

use common\helpers\TBaseHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class TBreadcrumbs extends BaseWidget
{
    /**
     * @var string the name of the breadcrumb container tag.
     */
    public $tag = 'ul';
    /**
     * @var array the HTML attributes for the breadcrumb container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'breadcrumb'];
    /**
     * @var boolean whether to HTML-encode the link labels.
     */
    public $encodeLabels = true;
    /**
     * @var array the first hyperlink in the breadcrumbs (called home link).
     * Please refer to [[links]] on the format of the link.
     * If this property is not set, it will default to a link pointing to [[\yii\web\Application::homeUrl]]
     * with the label 'Home'. If this property is false, the home link will not be rendered.
     */
    public $homeLink;

    public $moduleText;

    public $moduleTextTemplate = '<li><h2>{link}</h2></li>';
    /**
     * @var array list of links to appear in the breadcrumbs. If this property is empty,
     * the widget will not render anything. Each array element represents a single link in the breadcrumbs
     * with the following structure:
     *
     * ```php
     * [
     *     'label' => 'label of the link',  // required
     *     'url' => 'url of the link',      // optional, will be processed by Url::to()
     *     'template' => 'own template of the item', // optional, if not set $this->itemTemplate will be used
     * ]
     * ```
     *
     * If a link is active, you only need to specify its "label", and instead of writing `['label' => $label]`,
     * you may simply use `$label`.
     *
     * Since version 2.0.1, any additional array elements for each link will be treated as the HTML attributes
     * for the hyperlink tag. For example, the following link specification will generate a hyperlink
     * with CSS class `external`:
     *
     * ```php
     * [
     *     'label' => 'demo',
     *     'url' => 'http://example.com',
     *     'class' => 'external',
     * ]
     * ```
     *
     * Since version 2.0.3 each individual link can override global [[encodeLabels]] param like the following:
     *
     * ```php
     * [
     *     'label' => '<strong>Hello!</strong>',
     *     'encode' => false,
     * ]
     * ```
     *
     */
    public $links = [];
    /**
     * @var string the template used to render each inactive item in the breadcrumbs. The token `{link}`
     * will be replaced with the actual HTML link for each inactive item.
     */
    public $itemTemplate = "<li>{link}</li>\n";
    /**
     * @var string the template used to render each active item in the breadcrumbs. The token `{link}`
     * will be replaced with the actual HTML link for each active item.
     */
    public $activeItemTemplate = "<li class=\"active\">{link}</li>\n";


    /**
     * Renders the widget.
     */
    public function run()
    {
        if (empty($this->links)) {
            return;
        }
        $links = [];
        if (count($this->links) === 1) {
            $links[] = $this->renderItem(['label' => $this->links[0]], $this->moduleTextTemplate);
            unset($this->links[0]);
        } else {
            if (count($this->links) > 2) {
                $moduleIndex = count($this->links) - 2;
            } else {
                $moduleIndex = count($this->links) - 1;
            }
            $moduleIndex = $moduleIndex >= 0 ? $moduleIndex : 0;
            $links[] = $this->renderItem([
                'label' => $this->links[$moduleIndex],
            ], $this->moduleTextTemplate);
            unset($this->links[$moduleIndex]);
        }
        if ($this->homeLink === null) {
            $homePage = TBaseHelper::getHomePage();
            $links[] = $this->renderItem([
                'label' => Yii::t('common', 'home'),
                'url' => $homePage,
            ], $this->itemTemplate);
        } elseif ($this->homeLink !== false) {
            $links[] = $this->renderItem($this->homeLink, $this->itemTemplate);
        }
        foreach ($this->links as $link) {
            if (!is_array($link)) {
                $link = ['label' => $link];
            }
            $links[] = $this->renderItem($link, isset($link['url']) ? $this->itemTemplate : $this->activeItemTemplate);
        }
        echo Html::tag($this->tag, implode('', $links), $this->options);
    }

    /**
     * Renders a single breadcrumb item.
     * @param array $link the link to be rendered. It must contain the "label" element. The "url" element is optional.
     * @param string $template the template to be used to rendered the link. The token "{link}" will be replaced by the link.
     * @return string the rendering result
     * @throws InvalidConfigException if `$link` does not have "label" element.
     */
    protected function renderItem($link, $template)
    {
        $encodeLabel = $encode = ArrayHelper::remove($link, 'encode', $this->encodeLabels);
        if (array_key_exists('label', $link)) {
            $label = $encodeLabel ? Html::encode($link['label']) : $link['label'];
        } else {
            throw new InvalidConfigException('The "label" element is required for each link.');
        }
        if (isset($link['template'])) {
            $template = $link['template'];
        }
        if (isset($link['url'])) {
            $options = $link;
            unset($options['template'], $options['label'], $options['url']);
            $link = Html::a($label, $link['url'], $options);
        } else {
            $link = $label;
        }
        return strtr($template, ['{link}' => $link]);
    }
}
