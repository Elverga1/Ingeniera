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
<html>
<head>
    <title>Gestión de Préstamos</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #FF9800; color: white; }
        select, input, button { padding: 5px; margin: 5px; }
        .btn-eliminar { color: red; text-decoration: none; }
    </style>
</head>
<body>
    <h2>🔄 Gestión de Préstamos</h2>
    <a href="dashboard.php">← Volver al panel</a>
    
    <h3>Registrar nuevo préstamo</h3>
    <form method="POST">
        <select name="usuario_id" required>
            <option value="">Seleccionar usuario</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="libro_id" required>
            <option value="">Seleccionar libro</option>
            <?php foreach ($libros as $libro): ?>
                <option value="<?= $libro['id'] ?>"><?= htmlspecialchars($libro['titulo']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="fecha_prestamo" required>
        <input type="date" name="fecha_devolucion">
        <button type="submit" name="agregar">Registrar préstamo</button>
    </form>
    
    <h3>Lista de préstamos</h3>
    <table>
        <tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Fecha préstamo</th><th>Fecha devolución</th><th>Acciones</th></tr>
        <?php foreach ($prestamos as $prestamo): ?>
        <tr>
            <td><?= $prestamo['id'] ?></td>
            <td><?= htmlspecialchars($prestamo['usuario_nombre']) ?></td>
            <td><?= htmlspecialchars($prestamo['libro_titulo']) ?></td>
            <td><?= $prestamo['fecha_prestamo'] ?></td>
            <td><?= $prestamo['fecha_devolucion'] ?? 'Pendiente' ?></td>
            <td><a href="?eliminar=<?= $prestamo['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar este préstamo?')">Eliminar</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
