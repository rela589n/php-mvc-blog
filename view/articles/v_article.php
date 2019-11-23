<div class="post">
    <h2 class="title"><?= $article['title'] ?></h2>
    <p class="meta">
        <span class="date"><?= $article['dt'] ?> </span>
        <span class="posted">Posted by <?= $article['user_name'] ?></span>
    </p>
    <div style="clear: both;">&nbsp;</div>
    <div class="entry">
        <p><?= $article['content'] ?></p>

        <? if ($isAuth): ?>
            <p class="links">
                <? if ($isOwner): ?>
                    <a href="<?= ROOT ?>/article/edit/<?= $article['article_id'] ?>/" class="more">Edit</a>
                <? endif; ?>
            </p>
        <? endif; ?>
    </div>
</div>
