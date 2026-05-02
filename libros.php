<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $stmt = $pdo->prepare("INSERT INTO libros (titulo, autor, anio) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['titulo'], $_POST['autor'], $_POST['anio']]);
}

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM libros WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
}

$libros = $pdo->query("SELECT * FROM libros ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Libros</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .btn-eliminar { color: red; text-decoration: none; margin-left: 10px; }
        input, button { padding: 5px; margin: 5px; }
    </style>
</head>
<body>
    <h2>📖 Gestión de Libros</h2>
    <a href="dashboard.php">← Volver al panel</a>
    
    <h3>Agregar nuevo libro</h3>
    <form method="POST">
        <input type="text" name="titulo" placeholder="Título" required>
        <input type="text" name="autor" placeholder="Autor" required>
        <input type="number" name="anio" placeholder="Año">
        <button type="submit" name="agregar">Agregar libro</button>
    </form>
    
    <h3>Lista de libros</h3>
    <table>
        <tr><th>ID</th><th>Título</th><th>Autor</th><th>Año</th><th>Acciones</th></tr>
        <?php foreach ($libros as $libro): ?>
        <tr>
            <td><?= $libro['id'] ?></td>
            <td><?= htmlspecialchars($libro['titulo']) ?></td>
            <td><?= htmlspecialchars($libro['autor']) ?></td>
            <td><?= $libro['anio'] ?></td>
            <td><a href="?eliminar=<?= $libro['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar este libro?')">Eliminar</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
