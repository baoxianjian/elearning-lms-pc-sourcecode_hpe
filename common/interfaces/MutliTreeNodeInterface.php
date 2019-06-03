<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 4/6/2015
 * Time: 5:52 PM
 */

namespace common\interfaces;


interface MutliTreeNodeInterface {

    /**
     * 获取当前节点选中状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getSelectedStatus($kid, $nodeId);

    /**
     * 获取当前节点可用状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getDisabledStatus($kid, $nodeId);

    /**
     * 获取当前节点显示状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getDisplayedStatus($kid, $nodeId);

    /**
     * 获取当前节点打开状态
     * @param $kid
     * @param $nodeId
     * @return boolean
     */
    public function getOpenedStatus($kid, $nodeId);

    /**
     * 设置树节点上ID值的模式
     * 对于混合类型的树（即包括2种以上类型节点，则有可能出现ID一致无法判断的情况，所以需要增加树类型，以便区分）
     * 值格式为“树类型_ID”
     * @return boolean
     */
    public function isTreeNodeIdIncludeTreeType($kid, $nodeId);
}