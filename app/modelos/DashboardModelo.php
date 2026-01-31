<?php
// Archivo: app/modelos/DashboardModelo.php
class DashboardModelo
{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion = $db;
    }

    public function obtenerMetricas()
    {
        $enEst = (int)$this->scalar("SELECT COUNT(*) FROM ingresos_vehiculos WHERE estado = 'En Estacionamiento'");

        $salidasHoy = (int)$this->scalar(
            "SELECT COUNT(*) FROM salidas_vehiculos WHERE DATE(fecha_salida) = CURDATE()"
        );

        $ingresosHoy = (float)$this->scalar(
            "SELECT COALESCE(SUM(monto_total), 0) FROM salidas_vehiculos WHERE DATE(fecha_salida) = CURDATE()"
        );

        $promEstMin = (int)$this->scalar(
            "SELECT COALESCE(ROUND(AVG(minutos_totales)), 0)
             FROM salidas_vehiculos
             WHERE DATE(fecha_salida) = CURDATE()"
        );

        $pensAct = (int)$this->scalar(
            "SELECT COUNT(*) FROM pensiones WHERE esta_activa = 1"
        );

        $pensVencen = (int)$this->scalar(
            "SELECT COUNT(*)
             FROM pensiones
             WHERE esta_activa = 1
               AND vigencia_fin >= CURDATE()
               AND vigencia_fin <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );

        $usuariosAct = (int)$this->scalar(
            "SELECT COUNT(*) FROM usuarios WHERE activo = 1"
        );

        $admins = (int)$this->scalar(
            "SELECT COUNT(*) FROM usuarios WHERE activo = 1 AND rol = 'ADMIN'"
        );

        return [
            'en_estacionamiento' => $enEst,
            'prom_estancia_min' => $promEstMin,
            'ingresos_hoy' => round($ingresosHoy, 2),
            'salidas_hoy' => $salidasHoy,
            'pensiones_activas' => $pensAct,
            'pensiones_vencen_7_dias' => $pensVencen,
            'usuarios_activos' => $usuariosAct,
            'admins' => $admins
        ];
    }

    public function obtenerConfig()
    {
        $sql = "SELECT id, nombre_negocio, moneda_simbolo, nombre_impresora
                FROM configuracion_sistema
                ORDER BY id ASC
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'id' => null,
                'nombre_negocio' => '',
                'moneda_simbolo' => '$',
                'nombre_impresora' => 'POS-80'
            ];
        }

        return [
            'id' => isset($row['id']) ? (int)$row['id'] : null,
            'nombre_negocio' => (string)($row['nombre_negocio'] ?? ''),
            'moneda_simbolo' => (string)($row['moneda_simbolo'] ?? '$'),
            'nombre_impresora' => (string)($row['nombre_impresora'] ?? 'POS-80')
        ];
    }

    public function guardarConfig($nombre_negocio, $moneda_simbolo, $nombre_impresora)
    {
        $actual = $this->obtenerConfig();
        $id = $actual['id'];

        if ($id) {
            $sql = "UPDATE configuracion_sistema
                    SET nombre_negocio = ?,
                        moneda_simbolo = ?,
                        nombre_impresora = ?
                    WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$nombre_negocio, $moneda_simbolo, $nombre_impresora, (int)$id]);
        }

        $sql = "INSERT INTO configuracion_sistema (nombre_negocio, moneda_simbolo, nombre_impresora)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$nombre_negocio, $moneda_simbolo, $nombre_impresora]);
    }

    private function scalar($sql, $params = [])
    {
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();
        return ($val === false || $val === null) ? 0 : $val;
    }
}
