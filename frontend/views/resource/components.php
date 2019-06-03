<? if($components){?>
<div class="panel panel-default hotNews">
    <div class="panel-heading">
        <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('common',$component_name)?>
    </div>
    <div class="panel-body resourceStatu resourcePart">
        <ul>
            <?php  foreach($components as $component){?>
                <li class="component" title="<?=$component['title']?>" kid="<?=$component['kid']?>">
                    <?=$component['icon']?>
                    <a>
                        <p class="statuNum scrom"><?=$component['title']?></p>
                        <p><?=$component['description']?></p>
                    </a>
                </li>
            <? }?>
        </ul>
    </div>
</div>
<?}?>