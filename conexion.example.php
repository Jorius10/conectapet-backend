<?php
/**
 * conexion.example.php
 * Copia este archivo como "conexion.php" y rellena tus datos reales.
 */
$host     = 'localhost';
$db       = 'conectapet_db';     // Nombre de tu base de datos
$user     = 'root';              // Usuario MySQL
$password = '';                  // Contraseña MySQL

$conn = new mysqli($host, $user, $password, $db);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}
