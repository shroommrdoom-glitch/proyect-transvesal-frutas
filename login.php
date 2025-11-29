<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $userType = $_POST['userType'] ?? '';
    
    if (empty($email) || empty($password) || empty($userType)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }
    
    try {
        $conn = conectarDB();
        
        // Buscar usuario por email
        $stmt = $conn->prepare("SELECT u.*, ur.id_role, r.nombre as rol_nombre 
                                FROM usuarios u 
                                LEFT JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
                                LEFT JOIN roles r ON ur.id_role = r.id_role
                                WHERE u.email = :email AND u.activo = 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado o inactivo']);
            exit;
        }
        
        // Verificar contraseña (comparación directa para desarrollo)
        if ($password !== $user['hash_password']) {
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
            exit;
        }
        
        // Verificar que el tipo de usuario coincida con el rol
        if ($userType === 'admin' && $user['id_role'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Este usuario no es administrador']);
            exit;
        }
        
        if ($userType === 'cliente' && $user['id_role'] != 3) {
            echo json_encode(['success' => false, 'message' => 'Este usuario no es cliente']);
            exit;
        }
        
        // Login exitoso - guardar en sesión
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $userType;
        $_SESSION['role_id'] = $user['id_role'];
        
        echo json_encode([
            'success' => true, 
            'userType' => $userType,
            'userName' => $user['nombre']
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>