<?php
// app/modelos/LoginModelo.php
class LoginModelo {
  private $conexion;

  public function __construct($db) {
    $this->conexion = $db;
  }

  public function obtenerUsuarioPorUsuario($usuario) {
    $stmt = $this->conexion->prepare("
      SELECT id, nombre, usuario, password_hash, rol, activo
      FROM usuarios
      WHERE usuario = :usuario
      LIMIT 1
    ");
    $stmt->execute([':usuario' => $usuario]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  public function actualizarUltimoAcceso($id) {
    $stmt = $this->conexion->prepare("
      UPDATE usuarios
      SET ultimo_acceso = NOW()
      WHERE id = :id
      LIMIT 1
    ");
    $stmt->execute([':id' => (int)$id]);
    return $stmt->rowCount() >= 0;
  }
}
