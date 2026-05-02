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
            background: linear-gradient(145deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .floating-book {
            position: absolute;
            font-size: 3rem;
            opacity: 0.1;
            pointer-events: none;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.1; }
            90% { opacity: 0.1; }
            100% { transform: translateY(-20vh) rotate(360deg); opacity: 0; }
        }

        .book-1 { left: 5%; animation-duration: 18s; }
        .book-2 { left: 15%; animation-duration: 22s; animation-delay: 2s; }
        .book-3 { left: 25%; animation-duration: 20s; animation-delay: 5s; }
        .book-4 { right: 10%; animation-duration: 25s; animation-delay: 1s; }
        .book-5 { right: 20%; animation-duration: 16s; animation-delay: 7s; }

        .register-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            margin: 20px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.6);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px 40px;
            text-align: center;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: inherit;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
            opacity: 0.3;
        }

        .icon-badge {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .card-header h1 {
            color: white;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .card-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }

        .card-body {
            padding: 40px;
        }

        .input-group {
            margin-bottom: 24px;
        }

        .input-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: #1a1a2e;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .input-group label i {
            color: #667eea;
            font-size: 1rem;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
            font-family: inherit;
        }

        .input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-register:hover {
            transform: scale(0.98);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.5);
        }

        .login-link {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .login-link a:hover {
            color: #764ba2;
            gap: 12px;
        }

        .success-message {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #16a34a;
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #16a34a;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #dc2626;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
        }

        @media (max-width: 480px) {
            .card-header { padding: 35px 25px; }
            .card-body { padding: 30px 25px; }
            .card-header h1 { font-size: 1.8rem; }
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
