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

// Agregar autor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    
    if (empty($nombre)) {
        $error = "❌ El nombre del autor es obligatorio.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO autores (nombre) VALUES (?, ?)");
        if ($stmt->execute([$nombre])) {
            $mensaje = "✅ Autor agregado correctamente.";
        } else {
            $error = "❌ Error al agregar autor.";
        }
    }
}

// Eliminar autor
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    $check = $pdo->prepare("SELECT COUNT(*) FROM libros WHERE autor_id = ?");
    $check->execute([$id]);
    $tiene_libros = $check->fetchColumn();
    
    if ($tiene_libros > 0) {
        $error = "❌ No se puede eliminar el autor porque tiene libros asociados.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM autores WHERE id = ?");
        if ($stmt->execute([$id])) {
            $mensaje = "✅ Autor eliminado correctamente.";
        } else {
            $error = "❌ Error al eliminar autor.";
        }
    }
}

$autores = $pdo->query("SELECT * FROM autores ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Autores | Biblioteca Neon</title>
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

        .dashboard-container {
            position: relative;
            z-index: 2;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Top Bar Neon */
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

        /* Tarjetas */
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

        /* Formulario */
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

        input::placeholder {
            color: #00ffcc60;
        }

        /* Botones */
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
            font-family: inherit;
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        /* Tabla */
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

        /* Búsqueda */
        .search-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 280px;
            padding: 10px 16px;
        }

        /* Alertas */
        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
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

        /* Mensaje sin datos */
        .empty-table {
            text-align: center;
            padding: 50px;
            color: #00ffcc80;
        }

        .empty-table i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        /* Layout formulario */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .dashboard-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="top-bar">
            <h2><i class="fas fa-pen-fancy"></i> Gestión de Autores</h2>
            <div class="user-badge">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">
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

        <!-- Formulario agregar autor -->
        <div class="card">
            <h3 class="card-title"><i class="fas fa-plus-circle"></i> Agregar Nuevo Autor</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>📝 Nombre completo</label>
                        <input type="text" name="nombre" required placeholder="Ej: Gabriel García Márquez">
                    </div>
                    <div class="form-group">
                        <label>🌍 Nacionalidad</label>
                        <input type="text" name="nacionalidad" placeholder="Ej: Colombiana">
                    </div>
                    <div>
                        <button type="submit" name="agregar" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de autores -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 20px 0 20px;">
                <h3 class="card-title"><i class="fas fa-list"></i> Lista de Autores</h3>
            </div>
            
            <div class="search-box" style="padding: 0 20px;">
                <input type="text" id="searchInput" placeholder="🔍 Buscar autor por nombre..." onkeyup="buscarAutor()">
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Nacionalidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAutores">
                        <?php if (count($autores) > 0): ?>
                            <?php foreach ($autores as $autor): ?>
                                <tr class="autor-fila">
                                    <td style="color: #00ffcc;"><?= $autor['id'] ?></td>
                                    <td class="nombre-autor"><?= htmlspecialchars($autor['nombre']) ?></td>
                                    <td><?= htmlspecialchars($autor['nacionalidad'] ?: '—') ?></td>
                                    <td>
                                        <a href="?eliminar=<?= $autor['id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('⚠️ ¿Eliminar autor «<?= htmlspecialchars($autor['nombre']) ?>»?\n\nSi tiene libros asociados no podrá eliminarlo.')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-table">
                                    <i class="fas fa-users"></i>
                                    <p>No hay autores registrados.</p>
                                    <p style="font-size: 0.85rem;">Usa el formulario de arriba para agregar tu primer autor.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <a href="libros.php" class="btn btn-primary">
                <i class="fas fa-book"></i> Gestionar Libros
            </a>
        </div>
    </div>

    <script>
        function buscarAutor() {
            let input = document.getElementById('searchInput');
            let filtro = input.value.toLowerCase();
            let filas = document.getElementsByClassName('autor-fila');
            
            for (let i = 0; i < filas.length; i++) {
                let nombre = filas[i].getElementsByClassName('nombre-autor')[0];
                if (nombre) {
                    let texto = nombre.textContent || nombre.innerText;
                    if (texto.toLowerCase().indexOf(filtro) > -1) {
                        filas[i].style.display = '';
                    } else {
                        filas[i].style.display = 'none';
                    }
                }
            }
        }
    </script>
</body>
</html>
