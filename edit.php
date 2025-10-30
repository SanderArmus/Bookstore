<?php


require_once('./connection.php');

if (!isset($_GET['id']) || !$_GET['id']) {
    echo 'Viga: raamatut ei leitud!';
    exit();
}
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE books SET summary = :summary, price = :price , language =:language  WHERE id = :id');
    $stmt->execute(['summary'=>$_POST['summary'],'price'=>$_POST['price'],'language' =>$_POST['language'], 'id'=>$id]);
    
    
    $stmt = $pdo->prepare('DELETE FROM book_authors WHERE book_id = :book_id');
    $stmt->execute(['book_id' => $id]);
    
    $author_ids = $_POST['author_ids'] ?? [];
    $first_names = $_POST['first_names'] ?? [];
    $last_names = $_POST['last_names'] ?? [];
    
    foreach($first_names as $index => $first_name){
        $first_name = trim($first_name);
        $last_name = trim($last_names[$index]);

        var_dump($first_name, $last_name, $author_ids[$index] ?? 'new');
        
        if($first_name ==='' && $last_name ==='') continue;
        
        if(!empty($author_ids[$index])){
            $stmt = $pdo -> prepare('UPDATE authors SET first_name=:first_name, last_name=:last_name WHERE id =:id');
            $stmt->execute(['first_name'=>$first_name,'last_name'=>$last_name,'id'=>$author_ids[$index]]);
            
            $new_author_id = $author_ids[$index];
            
        } else {
            $stmt = $pdo->prepare('INSERT INTO authors(first_name, last_name) VALUES(:first_name, :last_name)');
            $stmt->execute(['first_name' => $first_name, 'last_name' => $last_name]);
            
            $new_author_id = $pdo->lastInsertId();
        }
        
        $stmt = $pdo->prepare('INSERT INTO book_authors (book_id, author_id) VALUES(:book_id, :author_id)');
        $stmt->execute(['book_id' => $id, 'author_id' => $new_author_id]);
    }
    





    header("Location: book.php?id=$id");
    exit();

}

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
$stmt->execute(['id' => $id]);
$book = $stmt->fetch();

$stmt = $pdo->query('SELECT id, first_name, last_name FROM authors ORDER BY last_name');
$all_authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT a.id, a.first_name, a.last_name FROM authors a LEFT JOIN book_authors ba ON  a.id=ba.author_id  WHERE ba.book_id = :book_id;');
$stmt->execute(['book_id' => $id]);
$current_authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muuda</title>
</head>
<body>
    <h1>Muuda Raamatut: <?= $book['title']?></h1>
    <form method="post">
        <label>Keel:</label><br>
        <input type="text" name="language" value="<?=$book['language']?>"><br><br>
        <label>Hind (€)</label><br>
        <input type="number" step="0.01" name="price" value="<?= number_format($book['price'], 2, '.', '') ?>"><br><br>
            <label>Autorid:</label><br>
            <?php foreach ($current_authors as $author): ?>
                <div>
                    <input type="hidden" name="author_ids[]" value="<?=$author['id']?>">
                    <label>Eesnimi:</label>
                    <input type="text" name="first_names[]" value="<?= $author['first_name']?>">
                    <label>Perekonnanimi:</label>
                    <input type="text" name="last_names[]" value="<?= $author['last_name']?>">
                </div>

                <?php endforeach;?>
            <div>
                <input type="hidden" name="author_ids[]" value="">
                <label>Lisa autor</label><br>
                <input type="text" name="first_names[]" placeholder="Eesnimi">
                <input type="text" name="last_names[]" placeholder="Perekonnanimi">
            </div>
        <label>Kirjeldus</label><br>
        <textarea name="summary" rows="5" cols="40"><?= htmlspecialchars($book['summary']) ?></textarea>


        <button type="submit">💾 Salvesta</button>
    </form>
    
</body>
</html>