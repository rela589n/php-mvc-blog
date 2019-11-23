<table>
    <? foreach ($texts as $text): ?>
        <tr>
            <td><?= $text['name'] ?>:</td>
            <td class="u"><?= $text['value'] ?></td>
            <td><a href="<?= ROOT ?>/dashboard/texts/edit/<?= $text['alias'] ?>">Редактировать</a></td>
        </tr>
    <? endforeach; ?>
</table>

