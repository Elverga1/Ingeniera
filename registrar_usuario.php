<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$nombre, $email, $password])) {
        echo "<h3>✅ Usuario registrado exitosamente</h3>";
        echo "<a href='login.php'>Ir a iniciar sesión</a>";
    } else {
        echo "<h3>❌ Error al registrar usuario</h3>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrar Usuario</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        input { margin: 5px; padding: 8px; }
        button { padding: 8px 15px; background: green; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Registro de Usuario</h2>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre completo" required><br>
        <input type="email" name="email" placeholder="Correo electrónico" required><br>
        <input type="password" name="password" placeholder="Contraseña" required><br>
        <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
</body>
</html>
