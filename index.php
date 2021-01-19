<?php require_once "autoloader.php" ?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php document_title(); ?>
    <?php favicon(); ?>
    
    <?php foreach($injected_css as $css) echo "<link rel='stylesheet' href='$css'>"?>
</head>
<body>
    <div class="container">
        <?php if(path_type($f) == "dir"): ?>
        <?php page_title(); bread_crumbs($f); ?>
        <table class="indexed-table">
            <tr><th> </th> <?php foreach($columns as $th) echo "<th>$th</th>"; ?> </tr>
            <?php foreach($data as $row): ?>
                <tr>
                    <td><?php echo getIcon(@$row["path"]); ?></td>
                    <?php foreach($columns as $th) echo $th == "name" 
                        ? "<td><a href='?f=".urlencode($row['inurl'])."'>".htmlentities($row[$th])."</a></td>" 
                        : "<td>".htmlentities($row[$th])."</td>"; 
                    ?> 
                </tr>
            <?php endforeach; ?>
        </table>
        <?php elseif(path_type($f) == "file"): ?>
            <h1>Invalid File</h1><a href='?'>Back to root parent</a>
        <?php else: ?>
            <h1>Invalid Path</h1>
            <a href="?">Back to root parent</a>
        <?php endif; ?>
    </div>
    <footer>Powered by <a href="https://eru123.github.io">eru123</a></footer>
</body>
</html>

