<? foreach ($articles as $article): ?>
    <? $link = ROOT . "article/{$article['article_id']}/";
        $edit_link = ROOT . "article/edit/{$article['article_id']}/"; ?>

    <div class="post">
        <h2 class="title"><a href="<?= $link ?>"><?= $article['title'] ?></a></h2>
        <p class="meta">
            <span class="date"><?= $article['dt'] ?></span>
            <span class="posted">Posted by <?= $article['user_name'] ?></span>
        </p>
        <div style="clear: both;">&nbsp;</div>
        <div class="entry">
            <p><?= $article['content'] ?></p>
            <p class="links">
                <a href="<?= $link ?>" class="more">Read More</a>
                <? if ($isAdmin || $userId === $article['id_user']) : ?>
                    <a href="<?= $edit_link ?>" class="more">Edit</a>
                <? endif; ?>
            </p>
        </div>
    </div>
<? endforeach; ?>