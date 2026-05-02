<?php
$host = 'localhost';
$dbname = 'sistema_prestamos';
$user = 'onofre';
$pass = 'SSJCRO16';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión a MariaDB: " . $e->getMessage());
}
?>
