<form method="post" class="simple_form">
    <label>
        Название<br>
        <input type="text" name="name" value="<?= $text['name'] ?>">
    </label><br>

    <label>
        Значение<br>
        <textarea name="value"><?= $text['value'] ?></textarea>
    </label><br>

    <input type="submit" value="Сохранить">
</form>
<div class="info">
    <?php echo $message; ?>
</div>
<a href="<?=ROOT?>dashboard/texts">К списку всех текстов</a>