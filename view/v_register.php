<form method="post" class="simple_form">
    <label>
        Username:<br>
        <input type="text" name="name" value="<?= $userName ?>">
    </label>

    <br>
    <label>
        Password:<br>
        <input type="password" name="password" value="<?= $first_password ?>">
    </label>

    <br>
    <label>
        Repeat Password:<br>
        <input type="password" name="re_password" value="<?= $second_password ?>">
    </label>

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
    <a href="<?= ROOT ?>auth/">Log in</a>
</p>

