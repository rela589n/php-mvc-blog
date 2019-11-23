<ul>
    <li><a href="<?= ROOT ?>/article/">Homepage</a></li>

    <? if ($isAuth): ?>
        <li><a href="<?=ROOT?>/article/add/">Add article</a></li>
        <li><a href="<?=ROOT?>/dashboard/">Dashboard</a></li>
        <li><a href="<?=ROOT?>/auth/">Exit</a></li>
    <? else: ?>
        <li><a href="<?=ROOT?>/auth/">Login</a></li>
    <? endif; ?>
</ul>
