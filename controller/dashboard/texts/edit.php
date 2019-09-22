<?php

use model\Texts;

$textAlias = $params[3] ?? null;

$text = '';
$mTexts = new Texts($db);
if ($textAlias == null || !($text = $mTexts->getById($textAlias))) {
    $error404 = true;
} else {
    $msg = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = secure_data($_POST['name']);
        $value = secure_data($_POST['value']);

        $mTexts->update($name, $value, $textAlias);
        $msg = SUCCESSFULLY_SAVED;
        $text['name'] = $name;
        $text['value'] = $value;
    }

    $content = get_template('dashboard/texts/v_edit.php', [
        'text' => $text,
        'message' => $msg
    ]);
}
