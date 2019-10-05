<form method="post" class="simple_form">

    <label>
        Название<br>
        <input type="text" name="title" value="<?= $title ?>">
    </label><br>
    <? if (isset($errors['title'])): ?>
        <div class="red">
            <?= $errors['title'] ?>
        </div>
        <br>
    <? endif; ?>

    <label>
        Контент<br>
        <textarea name="content"><?= $content ?></textarea>
    </label><br>
    <? if (isset($errors['content'])): ?>
        <div class="red">
            <?= $errors['content'] ?>
        </div>
        <br>
    <? endif; ?>

    <input type="submit" value="Сохранить">
</form>
<div class="info">
    <?php echo $message; ?>
</div>
