<table class="pure-table pure-table-bordered deploy">
    <thead>
    <tr>
        <th>Name</th>
<!--        <th>update</th>-->
<!--        <th>get</th>-->
    </tr>
    </thead>
    <? foreach (array_reverse($data) as $row): ?>
        <tr>
            <td style="color: #111; font-size: 0.9em"><?=nl2br($row) ?></td>
        </tr>
    <? endforeach; ?>
</table>