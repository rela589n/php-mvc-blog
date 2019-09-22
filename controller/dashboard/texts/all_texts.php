<?php

use model\Texts;

$mTexts = new Texts($db);
$content = get_template('dashboard/texts/v_texts.php', [
    'texts' => $mTexts->getPreviews($mTexts->getAll())
]);