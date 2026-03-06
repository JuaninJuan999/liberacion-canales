<?php

// Conexión directa a MySQL
$host = '127.0.0.1';
$db = 'liberacion_canales';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Actualizar o insertar módulo Hallazgos Tolerancia Cero
    $sql1 = "INSERT INTO menu_modulos (nombre, vista, icono, orden, roles, created_at, updated_at) 
             VALUES (:nombre, :vista, :icono, :orden, :roles, NOW(), NOW())
             ON DUPLICATE KEY UPDATE 
             vista = VALUES(vista), 
             icono = VALUES(icono), 
             orden = VALUES(orden),
             roles = VALUES(roles),
             updated_at = NOW()";
    
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute([
        ':nombre' => 'Hallazgos Tolerancia Cero',
        ':vista' => 'tolerancia-cero.registrar',
        ':icono' => 'exclamation-triangle',
        ':orden' => 9,
        ':roles' => json_encode(['Admin', 'Operaciones'])
    ]);
    
    echo "✅ Módulo 'Hallazgos Tolerancia Cero' creado/actualizado\n";
    echo "   Roles: Admin, Operaciones\n\n";

    // Actualizar o insertar módulo Historial Registros TC
    $sql2 = "INSERT INTO menu_modulos (nombre, vista, icono, orden, roles, created_at, updated_at) 
             VALUES (:nombre, :vista, :icono, :orden, :roles, NOW(), NOW())
             ON DUPLICATE KEY UPDATE 
             vista = VALUES(vista), 
             icono = VALUES(icono), 
             orden = VALUES(orden),
             roles = VALUES(roles),
             updated_at = NOW()";
    
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        ':nombre' => 'Historial Registros TC',
        ':vista' => 'tolerancia-cero.historial',
        ':icono' => 'clock',
        ':orden' => 10,
        ':roles' => json_encode(['Admin', 'Operaciones', 'Calidad', 'Gerencia'])
    ]);
    
    echo "✅ Módulo 'Historial Registros TC' creado/actualizado\n";
    echo "   Roles: Admin, Operaciones, Calidad, Gerencia\n\n";
    echo "✅ Módulos de Tolerancia Cero configurados correctamente\n";

} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}
