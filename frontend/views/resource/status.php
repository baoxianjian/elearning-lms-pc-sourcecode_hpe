<? foreach($resourceStatus as $status){?>
<li>
    <?= $status->icon?>
    <a href="<?= $status->url?>">
        <p class="statuNum"><?= $status->count?></p>
        <p><?= $status->title?></p>
    </a>
</li>
<? }?>
