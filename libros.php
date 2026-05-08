<?php
session_start();
require_once 'conexion.php';

// Verificar sesión o cookie
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['sesion_biblioteca'])) {
        $parts = explode('|', $_COOKIE['sesion_biblioteca']);
        if (count($parts) >= 3) {
            $_SESSION['user_id'] = $parts[0];
            $_SESSION['user_name'] = $parts[1];
            $_SESSION['user_email'] = $parts[2];
        } else {
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

$mensaje = '';
$error = '';

// Verificar si la tabla autores existe y tiene datos
try {
    $autores = $pdo->query("SELECT id, nombre FROM autores ORDER BY nombre")->fetchAll();
} catch (PDOException $e) {
    $autores = [];
    $error = "⚠️ Error con la tabla autores: " . $e->getMessage();
}

// AGREGAR LIBRO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $titulo = trim($_POST['titulo']);
    $autor_id = $_POST['autor_id'];
    $isbn = trim($_POST['isbn']);
    $anio = $_POST['anio'];
    
    if (empty($titulo)) {
        $error = "❌ El título del libro es obligatorio.";
    } elseif (empty($autor_id)) {
        $error = "❌ Debes seleccionar un autor.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO libros (titulo, autor_id, isbn, anio) VALUES (?, ?, ?, ?)");
            $stmt->execute([$titulo, $autor_id, $isbn ?: null, $anio ?: null]);
            $mensaje = "✅ Libro agregado correctamente.";
            // Redirigir para evitar reenvío
            header('Location: libros.php');
            exit;
        } catch (PDOException $e) {
            $error = "❌ Error al agregar libro: " . $e->getMessage();
        }
    }
}

// ELIMINAR LIBRO (CORREGIDO)
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    try {
        // Verificar si el libro tiene préstamos activos
        $check = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE libro_id = ? AND estado = 'activo'");
        $check->execute([$id]);
        $tiene_prestamos = $check->fetchColumn();
        
        if ($tiene_prestamos > 0) {
            $error = "❌ No se puede eliminar el libro porque tiene préstamos activos.";
        } else {
            // Eliminar el libro
            $stmt = $pdo->prepare("DELETE FROM libros WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = "✅ Libro eliminado correctamente.";
            // Redirigir para evitar reenvío
            header('Location: libros.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "❌ Error al eliminar libro: " . $e->getMessage();
    }
}

// OBTENER LIBROS (con nombre del autor)
try {
    $libros = $pdo->query("
        SELECT l.*, a.nombre as autor_nombre 
        FROM libros l 
        LEFT JOIN autores a ON l.autor_id = a.id 
        ORDER BY l.id DESC
    ")->fetchAll();
} catch (PDOException $e) {
    $libros = [];
    $error = "⚠️ Error al cargar libros: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libros | Biblioteca Neon</title>
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
            background: radial-gradient(circle at center, #0a0f1e 0%, #03050b 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(#00ffcc20 1px, transparent 1px), linear-gradient(90deg, #00ffcc20 1px, transparent 1px);
            background-size: 40px 40px;
            animation: gridMove 20s linear infinite;
            pointer-events: none;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(40px, 40px); }
        }

        .dashboard-container {
            position: relative;
            z-index: 2;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .top-bar {
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #00ffcc;
            border-radius: 32px;
            padding: 15px 25px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 0 30px #00ffcc40;
        }

        .top-bar h2 {
            color: #00ffcc;
            text-shadow: 0 0 10px #00ffcc;
            font-size: 1.5rem;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #00ffcc20;
            border: 1px solid #00ffcc;
            padding: 8px 18px;
            border-radius: 50px;
            color: #00ffcc;
        }

        .card {
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

        .card-title {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: #00ffcc;
            text-shadow: 0 0 5px #00ffcc;
            border-bottom: 2px solid #00ffcc;
            padding-bottom: 10px;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            color: #00ffcc;
            margin-bottom: 8px;
            display: block;
        }

        input, select {
            width: 100%;
            padding: 12px 16px;
            background: #00ffcc10;
            border: 1px solid #00ffcc;
            border-radius: 16px;
            color: #00ffcc;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ff00cc;
            box-shadow: 0 0 15px #ff00cc;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: linear-gradient(90deg, #00ffcc, #00ccaa);
            color: #0a0f1e;
        }

        .btn-success:hover {
            box-shadow: 0 0 20px #00ffcc;
            transform: scale(0.98);
        }

        .btn-danger {
            background: #ff00cc20;
            border: 1px solid #ff00cc;
            color: #ff00cc;
        }

        .btn-danger:hover {
            background: #ff00cc;
            color: #0a0f1e;
            box-shadow: 0 0 20px #ff00cc;
        }

        .btn-primary {
            background: #00ffcc20;
            border: 1px solid #00ffcc;
            color: #00ffcc;
        }

        .btn-primary:hover {
            background: #00ffcc;
            color: #0a0f1e;
            box-shadow: 0 0 20px #00ffcc;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 14px 16px;
            color: #00ffcc;
            border-bottom: 1px solid #00ffcc;
            font-weight: 600;
        }

        td {
            padding: 12px 16px;
            color: #cc88ff;
            border-bottom: 1px solid #00ffcc40;
        }

        tr:hover td {
            background: #00ffcc10;
            color: #00ffcc;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #00ffcc20;
            border-left: 4px solid #00ffcc;
            color: #00ffcc;
        }

        .alert-error {
            background: #ff00cc20;
            border-left: 4px solid #ff00cc;
            color: #ff00cc;
        }

        .empty-table {
            text-align: center;
            padding: 50px;
            color: #00ffcc80;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 15px;
            align-items: end;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="top-bar">
            <h2><i class="fas fa-book"></i> Gestión de Libros</h2>
            <div class="user-badge">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" class="btn btn-danger" style="padding: 5px 12px;">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Formulario agregar libro -->
        <div class="card">
            <h3 class="card-title"><i class="fas fa-plus-circle"></i> Agregar Nuevo Libro</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>📖 Título</label>
                        <input type="text" name="titulo" required placeholder="Ej: Cien años de soledad">
                    </div>
                    <div class="form-group">
                        <label>✍️ Autor</label>
                        <select name="autor_id" required>
                            <option value="">Seleccione un autor</option>
                            <?php foreach ($autores as $autor): ?>
                                <option value="<?= $autor['id'] ?>"><?= htmlspecialchars($autor['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>🔢 ISBN</label>
                        <input type="text" name="isbn" placeholder="978-3-16-148410-0">
                    </div>
                    <div>
                        <button type="submit" name="agregar" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de libros -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 20px 0 20px;">
                <h3 class="card-title"><i class="fas fa-list"></i> Lista de Libros</h3>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>ISBN</th>
                            <th>Año</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($libros) > 0): ?>
                            <?php foreach ($libros as $libro): ?>
                            <tr>
                                <td style="color: #00ffcc;"><?= $libro['id'] ?></td>
                                <td><?= htmlspecialchars($libro['titulo']) ?></td>
                                <td><?= htmlspecialchars($libro['autor_nombre'] ?? 'Sin autor') ?></td>
                                <td><?= htmlspecialchars($libro['isbn'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($libro['anio'] ?? '—') ?></td>
                                <td>
                                    <a href="?eliminar=<?= $libro['id'] ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 12px;"
                                       onclick="return confirm('⚠️ ¿Eliminar el libro «<?= htmlspecialchars($libro['titulo']) ?>»?\n\nEsta acción no se puede deshacer.')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-table">
                                    <i class="fas fa-book" style="font-size: 2rem;"></i>
                                    <p>No hay libros registrados.</p>
                                    <p style="font-size: 0.85rem;">Usa el formulario de arriba para agregar tu primer libro.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 15px;">
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <a href="autores.php" class="btn btn-primary">
                <i class="fas fa-pen-fancy"></i> Gestionar Autores
            </a>
        </div>
    </div>
</body>
</html>
