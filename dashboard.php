<?php
session_start();
require_once 'conexion.php';

// Verificar cookie si no hay sesión
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

// Contar registros para estadísticas
try {
    $totalLibros = $pdo->query("SELECT COUNT(*) FROM libros")->fetchColumn();
} catch (PDOException $e) {
    $totalLibros = 0;
}

try {
    $totalAutores = $pdo->query("SELECT COUNT(*) FROM autores")->fetchColumn();
} catch (PDOException $e) {
    $totalAutores = 0;
}

try {
    $totalPrestamos = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'activo'")->fetchColumn();
} catch (PDOException $e) {
    $totalPrestamos = 0;
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Biblioteca Neon</title>
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

        /* Libros flotantes neon */
        .floating-book {
            position: absolute;
            font-size: 3rem;
            opacity: 0.1;
            pointer-events: none;
            animation: float 20s infinite linear;
            filter: drop-shadow(0 0 10px #00ffcc);
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.15; }
            90% { opacity: 0.15; }
            100% { transform: translateY(-20vh) rotate(360deg); opacity: 0; }
        }

        .book-1 { left: 5%; animation-duration: 18s; }
        .book-2 { left: 15%; animation-duration: 22s; animation-delay: 2s; }
        .book-3 { left: 85%; animation-duration: 20s; animation-delay: 5s; }
        .book-4 { right: 10%; animation-duration: 25s; animation-delay: 1s; }

        .dashboard-container {
            position: relative;
            z-index: 2;
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Header Neon */
        .header {
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #00ffcc;
            border-radius: 32px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 0 30px #00ffcc40;
        }

        .logo-area h1 {
            font-size: 1.5rem;
            color: #00ffcc;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 0 8px #00ffcc;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(90deg, #00ffcc20, #ff00cc20);
            border: 1px solid #00ffcc;
            padding: 8px 20px;
            border-radius: 50px;
            color: #00ffcc;
        }

        .user-badge i {
            font-size: 1.2rem;
            color: #ff00cc;
        }

        .btn-logout {
            background: #ff00cc20;
            border: 1px solid #ff00cc;
            color: #ff00cc;
            padding: 10px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: #ff00cc;
            color: #0a0f1e;
            box-shadow: 0 0 20px #ff00cc;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 45px;
        }

        .stat-card {
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #00ffcc;
            border-radius: 24px;
            padding: 30px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 40px #00ffcc60;
            border-color: #ff00cc;
        }

        .stat-card i {
            font-size: 2.8rem;
            color: #00ffcc;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            font-size: 2.2rem;
            color: #00ffcc;
            margin-bottom: 8px;
            text-shadow: 0 0 8px #00ffcc;
        }

        .stat-card p {
            color: #cc88ff;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Menu Cards */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .menu-card {
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #00ffcc;
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 0 45px #00ffcc70;
            border-color: #ff00cc;
        }

        .card-icon {
            background: linear-gradient(135deg, #00ffcc20, #ff00cc20);
            padding: 35px;
            text-align: center;
            border-bottom: 1px solid #00ffcc;
        }

        .card-icon i {
            font-size: 3.2rem;
            color: #00ffcc;
        }

        .card-content {
            padding: 25px;
        }

        .card-content h3 {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: #00ffcc;
        }

        .card-content p {
            color: #cc88ff;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        /* Cookie Info */
        .cookie-info {
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #00ffcc;
            border-radius: 16px;
            padding: 12px 20px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #00ffcc;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .cookie-info i {
            font-size: 1rem;
        }

        /* Mensaje de bienvenida */
        .welcome-message {
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .dashboard-container { padding: 15px; }
            .header { flex-direction: column; text-align: center; }
            .stats-grid { gap: 15px; }
            .menu-grid { gap: 20px; }
        }
    </style>
</head>
<body>
    <div class="floating-book book-1">📖</div>
    <div class="floating-book book-2">📚</div>
    <div class="floating-book book-3">📕</div>
    <div class="floating-book book-4">📗</div>

    <div class="dashboard-container">
        <div class="header">
            <div class="logo-area">
                <h1>
                    <i class="fas fa-book-reader"></i> 
                    Biblioteca Neon
                </h1>
            </div>
            <div class="user-info">
                <div class="user-badge">
                    <i class="fas fa-user-astronaut"></i>
                    <span><?= htmlspecialchars($user_name) ?></span>
                    <span style="font-size: 0.7rem; opacity: 0.7;"><?= htmlspecialchars($user_email) ?></span>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>

        <!-- Mensaje de bienvenida -->
        <div class="welcome-message">
            <div class="alert alert-success" style="background: #00ffcc10; border-left: 4px solid #00ffcc; padding: 12px 18px; border-radius: 16px;">
                <i class="fas fa-hand-peace"></i> ¡Bienvenido de vuelta, <strong><?= htmlspecialchars($user_name) ?></strong>! Hoy es <?= date('d/m/Y') ?>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <h3><?= $totalLibros ?></h3>
                <p>📚 Libros disponibles</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3><?= $totalAutores ?></h3>
                <p>✍️ Autores registrados</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-hand-holding-heart"></i>
                <h3><?= $totalPrestamos ?></h3>
                <p>🔄 Préstamos activos</p>
            </div>
        </div>

        <!-- Menú principal -->
        <div class="menu-grid">
            <a href="libros.php" class="menu-card">
                <div class="card-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="card-content">
                    <h3><i class="fas fa-fw fa-plus-circle"></i> Gestión de Libros</h3>
                    <p>Agrega, edita o elimina libros de tu biblioteca. Mantén tu catálogo actualizado.</p>
                </div>
            </a>

            <a href="autores.php" class="menu-card">
                <div class="card-icon">
                    <i class="fas fa-pen-fancy"></i>
                </div>
                <div class="card-content">
                    <h3><i class="fas fa-fw fa-user-edit"></i> Gestión de Autores</h3>
                    <p>Administra los autores de tu colección. Registra nuevos escritores.</p>
                </div>
            </a>

            <a href="prestamos.php" class="menu-card">
                <div class="card-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="card-content">
                    <h3><i class="fas fa-fw fa-calendar-check"></i> Gestión de Préstamos</h3>
                    <p>Controla los préstamos de libros, fechas de devolución y seguimiento.</p>
                </div>
            </a>

            <a href="registrar_usuario.php" class="menu-card">
                <div class="card-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="card-content">
                    <h3><i class="fas fa-fw fa-user-plus"></i> Registrar Usuario</h3>
                    <p>Crea nuevas cuentas de usuario para el sistema biblioteca.</p>
                </div>
            </a>
        </div>

        <!-- Información de cookies (cumple con la rúbrica) -->
        <div class="cookie-info">
            <i class="fas fa-cookie-bite"></i>
            <span>🍪 Estado de cookies:</span>
            <span>🔹 sesion_biblioteca: <?= isset($_COOKIE['sesion_biblioteca']) ? '<span style="color: #00ffcc;">✓ Activa</span>' : '<span style="color: #ff00cc;">✗ No</span>' ?></span>
            <span>🔹 user_email: <?= isset($_COOKIE['user_email']) ? '<span style="color: #00ffcc;">✓ Activa</span>' : '<span style="color: #ff00cc;">✗ No</span>' ?></span>
            <span>🔹 user_name: <?= isset($_COOKIE['user_name']) ? '<span style="color: #00ffcc;">✓ Activa</span>' : '<span style="color: #ff00cc;">✗ No</span>' ?></span>
        </div>
    </div>

    <script>
        // Efecto de glow dinámico en las tarjetas de estadísticas
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
            });
        });
    </script>
</body>
</html>
