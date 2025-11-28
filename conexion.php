<?php

define('DB_HOST', 'mysql-sadboyz.alwaysdata.net');  // Host MySQL de AlwaysData
define('DB_NAME', 'sadboyz_de_transversal_definitiva');      // Tu base de datos
define('DB_USER', 'sadboyz');                        // Tu usuario
define('DB_PASS', 'SPKDENJI27/_/');                   // Tu contraseña

function conectarDB() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Función para redireccionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para formatear fecha
function formatearFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}

// Función para formatear precio
function formatearPrecio($precio) {
    return '$' . number_format($precio, 2);
}
?>