<?php
$host = 'localhost';
$dbname = 'masterkis'; // Nom de la base de données mis à jour
$user = 'root';
$pass = ''; // adapte selon ton installation

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
