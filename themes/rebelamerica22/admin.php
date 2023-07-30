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
    </body>


</html>