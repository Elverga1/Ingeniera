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
    $nacionalidad = trim($_POST['nacionalidad']);
    
    if (empty($nombre)) {
        $error = "El nombre del autor es obligatorio.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO autores (nombre, nacionalidad) VALUES (?, ?)");
        if ($stmt->execute([$nombre, $nacionalidad])) {
            $mensaje = "✅ Autor agregado correctamente.";
        } else {
            $error = "❌ Error al agregar autor.";
        }
    }
}

// Eliminar autor
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // Verificar si el autor tiene libros asociados
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

// Obtener todos los autores
$autores = $pdo->query("SELECT * FROM autores ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Autores | Biblioteca Académica</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos específicos para la tabla de autores */
        .table-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        .empty-table {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .search-box {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .search-box input {
            width: 250px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
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

        <!-- Formulario para agregar autor -->
        <div class="card">
            <h3 class="card-title"><i class="fas fa-plus-circle"></i> Agregar Nuevo Autor</h3>
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" required placeholder="Ej: Gabriel García Márquez">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Nacionalidad</label>
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
                <input type="text" id="searchInput" placeholder="🔍 Buscar autor..." onkeyup="buscarAutor()">
            </div>
            
            <div class="table-container" style="border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Nacionalidad</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAutores">
                        <?php if (count($autores) > 0): ?>
                            <?php foreach ($autores as $autor): ?>
                                <tr class="autor-fila">
                                    <td><?= $autor['id'] ?></td>
                                    <td class="nombre-autor"><?= htmlspecialchars($autor['nombre']) ?></td>
                                    <td><?= htmlspecialchars($autor['nacionalidad'] ?: '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($autor['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="?eliminar=<?= $autor['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('¿Eliminar autor \'<?= htmlspecialchars($autor['nombre']) ?>\'?\n\nSi tiene libros asociados no podrá eliminarlo.')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-table">
                                    <i class="fas fa-users" style="font-size: 2rem; color: #cbd5e1;"></i>
                                    <p>No hay autores registrados.</p>
                                    <p style="font-size: 0.85rem;">Usa el formulario de arriba para agregar tu primer autor.</p>
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
