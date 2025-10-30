<?php

require_once 'connection.php';

$search = $_GET['q'] ?? '';

if($search){
    $stmt = $pdo->prepare('SELECT id, title FROM books Where title LIKE :search ORDER BY title');
    $stmt->execute(['search' => "%$search%"]);
}else{
    $stmt = $pdo->query('SELECT id, title FROM books ORDER BY title');
}
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raamatupood</title>
</head>
<body>
    <h1>Raamatud</h1>
    <form method="get"action="index.php">
    <input type="text" name="q" placeholder="Otsi raamatut" value="<?=$search?>">
    <button type="submit">🔍 Otsi</button>
</form>

<ul>
    <?php if($books):?>
        <?php foreach($books as $book):?>
        <li><a href="book.php?id=<?= $book['id'] ?>"><?=($book['title']) ?></a></li>
        <?php endforeach; ?>
    <?php else: ?>
    <li>Raamatuid ei leitud</li>
    <?php endif; ?>
</ul>
</body>
</html>