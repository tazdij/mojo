<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?=$this->e($title)?></title>
    </head>
    <body>
        <h2>Admin Template</h2>
        <p><?=$this->e($title)?></p>
        <pre>
            <?=forms_create_nonce('test', 30)?>
        </pre>
        <h3>Contexts</h3>
        <table>
            <thead>
                <tr>
                    <th>ContextID</th>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Domain</th>
                    <th>Theme</th>
                    <th>Priority</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contexts as $ctx): ?>
                <tr>
                    <td><?=bin2hex($ctx['ContextID'])?></td>
                    <td><?=$ctx['Name']?></td>
                    <td><?=$ctx['Title']?></td>
                    <td><?=$ctx['Domain']?></td>
                    <td><?=$ctx['Theme']?></td>
                    <td><?=$ctx['Priority']?></td>
                    <td><?=$ctx['Description']?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>


</html>