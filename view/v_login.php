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
        <input type="password" name="password" value="<?= $password ?>">
    </label>

    <? if (isset($errors['password'])): ?>
        <div class="red">
            <?= $errors['password'] ?>
        </div>
        <br>
    <? endif; ?>
    <br>
    <label>
        Remember me
        <input type="checkbox" name="remember">
    </label>
    <br>
    <br>
    <input type="submit" value="Log in">
</form>
<br>

<div class="info">
    <?= $message ?>
</div>

<p>
    Still have not account?
    <a href="<?= ROOT ?>auth/register/">Register now!</a>
</p>

