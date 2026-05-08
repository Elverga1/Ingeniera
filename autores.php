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
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        background: radial-gradient(circle at center, #0a0f1e 0%, #03050b 100%);
        position: relative;
        overflow-x: hidden;
    }

    /* Grid neon de fondo */
    body::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background-image: 
            linear-gradient(#00ffcc20 1px, transparent 1px),
            linear-gradient(90deg, #00ffcc20 1px, transparent 1px);
        background-size: 40px 40px;
        animation: gridMove 20s linear infinite;
        pointer-events: none;
    }

    @keyframes gridMove {
        0% { transform: translate(0, 0); }
        100% { transform: translate(40px, 40px); }
    }

    .container {
        position: relative;
        z-index: 2;
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
    }

    /* Tarjetas neon */
    .card, .table-container, form {
        background: rgba(10, 15, 30, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid #00ffcc;
        border-radius: 24px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 0 20px #00ffcc40;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0 40px #00ffcc80;
        border-color: #ff00cc;
    }

    h1, h2, h3 {
        color: #00ffcc;
        text-shadow: 0 0 10px #00ffcc;
        margin-bottom: 20px;
    }

    /* Botones */
    .btn {
        background: linear-gradient(90deg, #00ffcc20, #ff00cc20);
        border: 1px solid #00ffcc;
        color: #00ffcc;
        padding: 10px 24px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .btn:hover {
        background: linear-gradient(90deg, #00ffcc, #ff00cc);
        color: #0a0f1e;
        box-shadow: 0 0 20px #00ffcc;
    }

    .btn-danger {
        border-color: #ff00cc;
        color: #ff00cc;
    }

    .btn-danger:hover {
        background: #ff00cc;
        color: #0a0f1e;
    }

    /* Tablas */
    table {
        width: 100%;
        border-collapse: collapse;
        color: #cc88ff;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #00ffcc40;
    }

    th {
        color: #00ffcc;
        font-weight: 600;
    }

    tr:hover {
        background: #00ffcc10;
    }

    /* Inputs */
    input, select, textarea {
        width: 100%;
        padding: 12px 16px;
        background: rgba(0, 255, 204, 0.05);
        border: 1px solid #00ffcc;
        border-radius: 16px;
        color: #00ffcc;
        font-family: inherit;
        margin-bottom: 15px;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #ff00cc;
        box-shadow: 0 0 15px #ff00cc;
    }

    label {
        color: #00ffcc;
        display: block;
        margin-bottom: 5px;
    }

    /* Header de navegación */
    .navbar {
        background: rgba(10, 15, 30, 0.9);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #00ffcc;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 30px;
    }

    .navbar a {
        color: #00ffcc;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .navbar a:hover {
        color: #ff00cc;
        text-shadow: 0 0 8px #ff00cc;
    }

    .user-info {
        color: #00ffcc;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Mensajes de éxito/error */
    .success {
        background: #00ffcc20;
        border-left: 4px solid #00ffcc;
        color: #00ffcc;
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .error {
        background: #ff00cc20;
        border-left: 4px solid #ff00cc;
        color: #ff00cc;
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
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
