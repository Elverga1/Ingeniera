<?php
session_start();
require_once 'conexion.php';

$error = '';
$recordar = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $recordar = isset($_POST['recordarme']) ? true : false;
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        
        // COOKIE PRINCIPAL DE SESIÓN (requerida por rúbrica)
        $cookie_sesion = $user['id'] . '|' . $user['nombre'] . '|' . $user['email'];
        
        if ($recordar) {
            setcookie('user_email', $user['email'], time() + (86400 * 30), '/');
            setcookie('user_name', $user['nombre'], time() + (86400 * 30), '/');
            setcookie('sesion_biblioteca', $cookie_sesion, time() + (86400 * 30), '/');
        } else {
            setcookie('user_email', $user['email'], time() + 86400, '/');
            setcookie('user_name', $user['nombre'], time() + 86400, '/');
            setcookie('sesion_biblioteca', $cookie_sesion, time() + 86400, '/');
        }
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "❌ Correo o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Virtual | Neon</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
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
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(40px, 40px); }
        }

        /* Libros flotantes neon */
        .floating-book {
            position: absolute;
            font-size: 3rem;
            opacity: 0.2;
            pointer-events: none;
            animation: float 20s infinite linear;
            filter: drop-shadow(0 0 10px #00ffcc);
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-20vh) rotate(360deg); opacity: 0; }
        }

        .book-1 { left: 5%; animation-duration: 18s; }
        .book-2 { left: 15%; animation-duration: 22s; animation-delay: 2s; }
        .book-3 { left: 25%; animation-duration: 20s; animation-delay: 5s; }
        .book-4 { right: 10%; animation-duration: 25s; animation-delay: 1s; }
        .book-5 { right: 20%; animation-duration: 16s; animation-delay: 7s; }

        .login-wrapper {
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

        /* Tarjeta con glow neon */
        .login-card {
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 32px;
            border: 1px solid #00ffcc;
            box-shadow: 0 0 30px #00ffcc40, inset 0 0 20px #00ffcc10;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 50px #00ffcc80, inset 0 0 30px #00ffcc20;
        }

        .card-header {
            background: linear-gradient(135deg, #00ffcc20 0%, #ff00cc20 100%);
            padding: 50px 40px 40px;
            text-align: center;
            border-bottom: 1px solid #00ffcc;
        }

        .icon-badge {
            width: 80px;
            height: 80px;
            background: #00ffcc20;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            border: 1px solid #00ffcc;
            box-shadow: 0 0 15px #00ffcc;
            color: #00ffcc;
        }

        .card-header h1 {
            color: #00ffcc;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 0 10px #00ffcc;
            letter-spacing: 2px;
        }

        .card-header p {
            color: #cc88ff;
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
            color: #00ffcc;
            font-weight: 600;
            font-size: 0.9rem;
            text-shadow: 0 0 3px #00ffcc;
        }

        .input-group label i {
            color: #ff00cc;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 255, 204, 0.05);
            border: 1px solid #00ffcc;
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
            font-family: inherit;
            color: #00ffcc;
        }

        .input-group input::placeholder {
            color: #00ffcc60;
        }

        .input-group input:focus {
            border-color: #ff00cc;
            box-shadow: 0 0 15px #ff00cc;
            background: #00ffcc10;
        }

        .checkbox-group {
            margin-bottom: 28px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: #cc88ff;
            font-size: 0.9rem;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #00ffcc;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #00ffcc, #ff00cc);
            color: #0a0f1e;
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
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-login:hover {
            transform: scale(0.98);
            box-shadow: 0 0 30px #00ffcc, 0 0 15px #ff00cc;
        }

        .register-link {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #00ffcc40;
        }

        .register-link a {
            color: #00ffcc;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .register-link a:hover {
            color: #ff00cc;
            text-shadow: 0 0 8px #ff00cc;
            gap: 12px;
        }

        .error-message {
            background: #ff00cc20;
            color: #ff00cc;
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #ff00cc;
            backdrop-filter: blur(4px);
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            color: #00ffcc80;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="floating-book book-1">📖</div>
    <div class="floating-book book-2">📚</div>
    <div class="floating-book book-3">📕</div>
    <div class="floating-book book-4">📗</div>
    <div class="floating-book book-5">📘</div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="card-header">
                <div class="icon-badge">
                    <i class="fas fa-book-reader"></i>
                </div>
                <h1>Biblioteca Virtual</h1>
                <p>Tu mundo de conocimiento te espera</p>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                        <input type="email" name="email" placeholder="tu@correo.com" required>
                    </div>
                    
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="recordarme"> 
                            <i class="fas fa-clock"></i> Recuérdame
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                    </button>
                </form>
                
                <div class="register-link">
                    <a href="registrar_usuario.php">
                        <i class="fas fa-user-plus"></i> ¿No tienes cuenta? Crear cuenta
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-note">
            <i class="fas fa-shield-alt"></i> Sistema seguro | Biblioteca Digital
        </div>
    </div>
</body>
</html>
