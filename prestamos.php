<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $stmt = $pdo->prepare("INSERT INTO prestamos (usuario_id, libro_id, fecha_prestamo, fecha_devolucion) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['usuario_id'], $_POST['libro_id'], $_POST['fecha_prestamo'], $_POST['fecha_devolucion']]);
}

if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM prestamos WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
}

$prestamos = $pdo->query("
    SELECT p.*, u.nombre as usuario_nombre, l.titulo as libro_titulo 
    FROM prestamos p
    JOIN usuarios u ON p.usuario_id = u.id
    JOIN libros l ON p.libro_id = l.id
    ORDER BY p.id DESC
")->fetchAll();

$usuarios = $pdo->query("SELECT id, nombre FROM usuarios")->fetchAll();
$libros = $pdo->query("SELECT id, titulo FROM libros")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Préstamos | Biblioteca Virtual</title>
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
            max-width: 1300px;
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
        .form-card, .table-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .input-group select, .input-group input {
            width: 100%;
            padding: 12px;
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
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f7fafc; }
        .btn-delete { color: #dc2626; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-exchange-alt"></i> Gestión de Préstamos</h1>
            <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="form-card">
            <h3><i class="fas fa-plus-circle"></i> Nuevo préstamo</h3>
            <form method="POST" class="form-grid">
                <div class="input-group">
                    <label>Usuario</label>
                    <select name="usuario_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Libro</label>
                    <select name="libro_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($libros as $libro): ?>
                            <option value="<?= $libro['id'] ?>"><?= htmlspecialchars($libro['titulo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Fecha préstamo</label>
                    <input type="date" name="fecha_prestamo" required>
                </div>
                <div class="input-group">
                    <label>Fecha devolución</label>
                    <input type="date" name="fecha_devolucion">
                </div>
                <button type="submit" name="agregar" class="btn-submit">Registrar</button>
            </form>
        </div>

        <div class="table-card">
            <h3><i class="fas fa-list"></i> Historial de préstamos</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Préstamo</th><th>Devolución</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($prestamos as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['usuario_nombre']) ?></td>
                        <td><?= htmlspecialchars($p['libro_titulo']) ?></td>
                        <td><?= $p['fecha_prestamo'] ?></td>
                        <td><?= $p['fecha_devolucion'] ?? 'Pendiente' ?></td>
                        <td><a href="?eliminar=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('¿Eliminar este préstamo?')"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
