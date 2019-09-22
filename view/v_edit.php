<form method="post" class="simple_form">

    <label>
        Название<br>
        <input type="text" name="title" value="<?= $title ?>">
    </label><br>

    <label>
        Контент<br>
        <textarea name="content"><?= $content ?></textarea>
    </label><br>

    <input type="submit" value="Сохранить">
</form>
<div class="info">
    <?php echo $message; ?>
</div>
