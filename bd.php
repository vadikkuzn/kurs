<?php


try {
    $pdo = new PDO('mysql:dbname=kurs; host=localhost', 'vadim', 'dflbr123');
} catch (PDOException $e) {
    die($e->getMessage());
}

?>