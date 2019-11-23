<form method="post" class="simple_form">
    <label>
        Username:<br>
        <input type="text" name="name" value="<?= $userName ?>">
    </label>
    <? if (isset($errors['user_name'])): ?>
        <div class="red">
            <?= $errors['user_name'] ?>
        </div>
        <br>
    <? endif; ?>
    <br>
    <label>
        Password:<br>
        <input type="password" name="password" value="<?= $first_password ?>">
    </label>

    <? if (isset($errors['password'])): ?>
        <div class="red">
            <?= $errors['password'] ?>
        </div>
        <br>
    <? endif; ?>

    <br>
    <label>
        Repeat Password:<br>
        <input type="password" name="password_confirm" value="<?= $second_password ?>">
    </label>

    <? if (isset($errors['password_confirm'])): ?>
        <div class="red">
            <?= $errors['password_confirm'] ?>
        </div>
        <br>
    <? endif; ?>

    <br>
    <br>
    <input type="submit" value="Register">
</form>
<br>

<div class="info">
    <?= $message ?>
</div>

<p>
    Already have account?
    <a href="<?= ROOT ?>/auth/">Log in</a>
</p>

