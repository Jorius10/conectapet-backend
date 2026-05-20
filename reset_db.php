<?php
$host = "localhost";
$user = "root";       
$password = "";       
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Leemos el nuevo database.sql para forzar su instalación / sobreescritura
$sql_file = file_get_contents('database.sql');

if ($conn->multi_query($sql_file)) {
    do {
        if ($result = $conn->store_result()) { $result->free(); }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "<h1>¡Base de datos actualizada con éxito!</h1>";
    echo "<p>Las 14 mascotas reales y los 4 albergues ya están distribuidos usando tus imágenes.</p>";
    echo "<a href='albergues.php' style='padding: 10px 20px; background: #8b5cf6; color: white; border-radius: 20px; text-decoration: none; font-family: sans-serif; font-weight: bold;'>Volver a ver los Albergues</a>";
} else {
    echo "Error instalando la base de datos: " . $conn->error;
}
?>
