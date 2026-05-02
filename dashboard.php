<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel de Control</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        .menu { margin: 20px 0; }
        .menu a { margin-right: 15px; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .cookie-info { background: #f0f0f0; padding: 10px; margin: 20px 0; border-left: 4px solid green; }
    </style>
</head>
<body>
    <h2>📚 Bienvenido, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
    
    <div class="cookie-info">
        <strong>🍪 Información de cookies:</strong><br>
        Email (vía cookie): <?= $_COOKIE['user_email'] ?? 'No disponible' ?><br>
        Nombre (vía cookie): <?= $_COOKIE['user_name'] ?? 'No disponible' ?>
    </div>
    
    <div class="menu">
        <a href="libros.php">📖 Libros</a>
        <a href="autores.php">✍️ Autores</a>
        <a href="prestamos.php">🔄 Préstamos</a>
        <a href="logout.php">🚪 Cerrar sesión</a>
    </div>
    
    <h3>Sistema de Gestión de Biblioteca</h3>
    <p>Usuario ID: <?= $_SESSION['user_id'] ?></p>
</body>
</html>
