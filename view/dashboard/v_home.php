<h2>Привет, <?= $userName ?></h2>
<br>
<? if ($isAdmin): ?>
    <h3><a href="<?= ROOT ?>/dashboard/texts/">Тексты</a></h3>
<? endif; ?>