<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Agregar libro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $stmt = $pdo->prepare("INSERT INTO libros (titulo, autor, anio) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['titulo'], $_POST['autor'], $_POST['anio']]);
}

// Eliminar libro
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM libros WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
}

$libros = $pdo->query("SELECT * FROM libros ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Libros | Biblioteca Virtual</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(145deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            position: relative;
        }
        .floating-book {
            position: absolute;
            font-size: 3rem;
            opacity: 0.05;
            pointer-events: none;
            animation: float 20s infinite linear;
        }
        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); }
            100% { transform: translateY(-20vh) rotate(360deg); }
        }
        .book-1 { left: 5%; animation-duration: 18s; }
        .book-2 { left: 15%; animation-duration: 22s; animation-delay: 2s; }
        .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .header h1 { display: flex; align-items: center; gap: 10px; color: #1a1a2e; }
        .header h1 i { color: #667eea; }
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-card, .table-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        .input-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a2e;
        }
        .input-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .input-group input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #f7fafc;
            color: #1a1a2e;
        }
        .btn-delete {
            color: #dc2626;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-delete:hover { background: #fee2e2; }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .container { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="floating-book book-1">📖</div>
    <div class="floating-book book-2">📚</div>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-book"></i> Gestión de Libros</h1>
            <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al panel</a>
        </div>

        <div class="form-card">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> Agregar nuevo libro</h3>
            <form method="POST" class="form-grid">
                <div class="input-group">
                    <label><i class="fas fa-tag"></i> Título</label>
                    <input type="text" name="titulo" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-user"></i> Autor</label>
                    <input type="text" name="autor" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-calendar"></i> Año</label>
                    <input type="number" name="anio">
                </div>
                <button type="submit" name="agregar" class="btn-submit"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>

        <div class="table-card">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-list"></i> Catálogo de libros</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Título</th><th>Autor</th><th>Año</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($libros as $libro): ?>
                    <tr>
                        <td><?= $libro['id'] ?></td>
                        <td><?= htmlspecialchars($libro['titulo']) ?></td>
                        <td><?= htmlspecialchars($libro['autor']) ?></td>
                        <td><?= $libro['anio'] ?></td>
                        <td><a href="?eliminar=<?= $libro['id'] ?>" class="btn-delete" onclick="return confirm('¿Eliminar este libro?')"><i class="fas fa-trash"></i> Eliminar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
