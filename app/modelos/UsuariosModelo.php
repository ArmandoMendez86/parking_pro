<?php
// Archivo: app/modelos/UsuariosModelo.php

class UsuariosModelo
{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion = $db;
    }

    public function listar($q = '', $rol = '')
    {
        $q = trim((string)$q);
        $rol = trim((string)$rol);

        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = "(nombre LIKE ? OR usuario LIKE ?)";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }

        if ($rol !== '') {
            $where[] = "rol = ?";
            $params[] = $rol;
        }

        $sql = "SELECT id, nombre, usuario, rol, activo, ultimo_acceso
                FROM usuarios";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY activo DESC, nombre ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $sql = "SELECT id, nombre, usuario, rol, activo, ultimo_acceso
                FROM usuarios
                WHERE id = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function existeUsuarioLogin($usuario, $excluirId = 0)
    {
        $usuario = trim((string)$usuario);
        $excluirId = (int)$excluirId;

        if ($usuario === '') return false;

        if ($excluirId > 0) {
            $sql = "SELECT id FROM usuarios WHERE usuario = ? AND id <> ? LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$usuario, $excluirId]);
            return (bool)$stmt->fetch();
        }

        $sql = "SELECT id FROM usuarios WHERE usuario = ? LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario]);
        return (bool)$stmt->fetch();
    }

    public function crear($nombre, $usuario, $passwordHash, $rol, $activo)
    {
        $sql = "INSERT INTO usuarios (nombre, usuario, password_hash, rol, activo)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $ok = $stmt->execute([
            $nombre,
            $usuario,
            $passwordHash,
            $rol,
            (int)($activo ? 1 : 0)
        ]);

        if (!$ok) return false;
        return (int)$this->conexion->lastInsertId();
    }

    public function actualizar($id, $nombre, $usuario, $rol, $activo)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $sql = "UPDATE usuarios
                SET nombre = ?, usuario = ?, rol = ?, activo = ?
                WHERE id = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            $nombre,
            $usuario,
            $rol,
            (int)($activo ? 1 : 0),
            $id
        ]);

        return ($stmt->rowCount() >= 0);
    }

    public function actualizarPassword($id, $passwordHash)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $sql = "UPDATE usuarios
                SET password_hash = ?
                WHERE id = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$passwordHash, $id]);

        return ($stmt->rowCount() >= 0);
    }

    public function eliminar($id)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $sql = "DELETE FROM usuarios WHERE id = ? LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);

        return ($stmt->rowCount() > 0);
    }

    public function setActivo($id, $activo)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $sql = "UPDATE usuarios SET activo = ? WHERE id = ? LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([(int)($activo ? 1 : 0), $id]);

        return ($stmt->rowCount() >= 0);
    }
}
