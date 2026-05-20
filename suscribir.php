<?php
session_start();
require_once 'conexion.php';

// Crear tabla suscriptores si no existe
$conn->query("CREATE TABLE IF NOT EXISTS suscriptores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) UNIQUE NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$msg = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $msg  = 'Ingresa un correo válido.';
        $tipo = 'error';
    } else {
        $esc = $conn->real_escape_string($correo);
        // Verificar si ya está suscrito
        $existe = $conn->query("SELECT id FROM suscriptores WHERE correo='$esc'")->num_rows;
        if ($existe > 0) {
            $msg  = '¡Ya estás suscrito con ese correo! 🎉';
            $tipo = 'info';
        } else {
            $conn->query("INSERT INTO suscriptores (correo) VALUES ('$esc')");
            $msg  = '¡Gracias! Te has suscrito correctamente al boletín de ConectaPet 🐾';
            $tipo = 'ok';
        }
    }
}

// Redirigir de vuelta con mensaje
$_SESSION['boletin_msg']  = $msg;
$_SESSION['boletin_tipo'] = $tipo;
$redir = $_POST['redir'] ?? 'index.php';
header("Location: $redir");
exit;
