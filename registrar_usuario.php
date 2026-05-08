<?php
require_once 'conexion.php';

$exito = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->fetch()) {
        $error = "❌ Este correo ya está registrado";
    } else {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$nombre, $email, $password])) {
            $exito = "✅ Cuenta creada exitosamente";
        } else {
            $error = "❌ Error al crear la cuenta";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | Biblioteca Virtual</title>
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
    <div class="floating-book book-1">📖</div>
    <div class="floating-book book-2">📚</div>
    <div class="floating-book book-3">📕</div>
    <div class="floating-book book-4">📗</div>
    <div class="floating-book book-5">📘</div>

    <div class="register-wrapper">
        <div class="register-card">
            <div class="card-header">
                <div class="icon-badge">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Unete a nosotros</h1>
                <p>Crea tu cuenta y comienza a explorar</p>
            </div>
            <div class="card-body">
                <?php if ($exito): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <?= $exito ?>
                        <a href="login.php" style="color: #16a34a; margin-left: auto;">Iniciar sesión →</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="input-group">
                        <label><i class="fas fa-user"></i> Nombre completo</label>
                        <input type="text" name="nombre" placeholder="Juan Pérez" required>
                    </div>
                    
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                        <input type="email" name="email" placeholder="tu@correo.com" required>
                    </div>
                    
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus"></i> Crear cuenta
                    </button>
                </form>
                
                <div class="login-link">
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt"></i> ¿Ya tienes cuenta? Inicia sesión
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-note">
            <i class="fas fa-shield-alt"></i> Tus datos están seguros
        </div>
    </div>
</body>
</html>
