<?php

require_once('./connection.php');

if (!isset($_GET['id']) || !$_GET['id']) {
    echo 'Viga: raamatut ei leitud!';
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
$stmt->execute(['id' => $id]);
$book = $stmt->fetch();

$stmt = $pdo->prepare('SELECT first_name, last_name FROM book_authors ba LEFT JOIN authors a ON ba.author_id = a.id WHERE book_id = :book_id;');
$stmt->execute(['book_id' => $id]);
$authors = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT b.id, b.title, COUNT(o.book_id) AS order_count FROM books b LEFT JOIN orders o ON b.id = o.book_id WHERE b.id = :book_id GROUP BY b.id, b.title');
$stmt->execute(['book_id' => $id]);
$order_count = $stmt->fetch();


$stmt = $pdo->prepare('SELECT cover_path FROM books WHERE id= :id');
$stmt->execute(['id'=>$id]);
$cover_path = $stmt->fetch();
// var_dump($authors);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $book['title']; ?></title>
</head>

<body>
    <h1><?= $book['title']; ?></h1>
    <img src="<?= $cover_path['cover_path'] ?>" alt="">
    <p>Ilmumisaasta: <?= $book['release_date']; ?></p>
    <p>Keel: <?= $book['language']; ?></p>
    <p>Lehekülgi: <?= $book['pages']; ?></p>
    <p>Kirjeldus: <?= $book['summary']; ?></p>

    Autorid:
    <ul>
        <?php foreach ($authors as $author) { ?>
            <li><?= "{$author['first_name']} {$author['last_name']}"; ?></li>
        <?php } ?>
    </ul>

    <p>Hind <?= number_format($book['price'], 2,',', ' ')?>€</p>
    <p>Tellitud: <?= $order_count['order_count'] ?? 0 ?> Korda</p>

    <a href="./edit.php?id=<?= $book['id'] ?>">Muuda</a>


</body>

</html>