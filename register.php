<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    if (empty($nombre) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Nombre, email y contraseña son requeridos']);
        exit;
    }
    
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }
    
    try {
        $conn = conectarDB();
        
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
            exit;
        }
        
        // Hash de la contraseña
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, telefono, hash_password, activo) 
                                VALUES (:nombre, :email, :telefono, :hash_password, 1)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':hash_password' => $hash_password
        ]);
        
        $userId = $conn->lastInsertId();
        
        // Asignar rol de cliente (id_role = 3)
        $stmt = $conn->prepare("INSERT INTO usuario_rol (id_usuario, id_role) VALUES (:user_id, 3)");
        $stmt->execute([':user_id' => $userId]);
        
        // Crear registro en clientes
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, telefono) 
                                VALUES (:nombre, :email, :telefono)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Usuario registrado exitosamente',
            'userId' => $userId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>