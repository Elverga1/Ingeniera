<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $stmt = $pdo->prepare("INSERT INTO autores (nombre) VALUES (?)");
    $stmt->execute([$_POST['nombre']]);
}

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM autores WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
}

$autores = $pdo->query("SELECT * FROM autores ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Autores</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        table { border-collapse: collapse; width: 50%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #2196F3; color: white; }
        .btn-eliminar { color: red; text-decoration: none; }
        input, button { padding: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h2>✍️ Gestión de Autores</h2>
    <a href="dashboard.php">← Volver al panel</a>
    
    <h3>Agregar nuevo autor</h3>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre del autor" required>
        <button type="submit" name="agregar">Agregar autor</button>
    </form>
    
    <h3>Lista de autores</h3>
    <table>
        <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
        <?php foreach ($autores as $autor): ?>
        <tr>
            <td><?= $autor['id'] ?></td>
            <td><?= htmlspecialchars($autor['nombre']) ?></td>
            <td><a href="?eliminar=<?= $autor['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar este autor?')">Eliminar</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
