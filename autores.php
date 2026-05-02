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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Autores | Biblioteca Virtual</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(145deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }
        .header {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .header h1 { display: flex; align-items: center; gap: 10px; color: #1a1a2e; }
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 50px;
            text-decoration: none;
        }
        .form-card, .list-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-group {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        .form-group input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 16px;
            cursor: pointer;
        }
        .author-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .author-name { font-size: 1.1rem; font-weight: 500; }
        .btn-delete { color: #dc2626; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Gestión de Autores</h1>
            <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="form-card">
            <h3><i class="fas fa-plus-circle"></i> Agregar autor</h3>
            <form method="POST" class="form-group">
                <input type="text" name="nombre" placeholder="Nombre del autor" required>
                <button type="submit" name="agregar" class="btn-submit">Guardar</button>
            </form>
        </div>

        <div class="list-card">
            <h3><i class="fas fa-list"></i> Lista de autores</h3>
            <?php foreach ($autores as $autor): ?>
            <div class="author-item">
                <span class="author-name"><?= htmlspecialchars($autor['nombre']) ?></span>
                <a href="?eliminar=<?= $autor['id'] ?>" class="btn-delete" onclick="return confirm('¿Eliminar este autor?')"><i class="fas fa-trash"></i> Eliminar</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
