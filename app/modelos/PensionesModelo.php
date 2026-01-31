<?php
class PensionesModelo {
    private $conexion;

    public function __construct($db) {
        $this->conexion = $db;
    }

    /* =========================
       PENSIONES
       ========================= */

    public function listarPensiones($busqueda = '') {
        $busqueda = trim($busqueda);

        if ($busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT 
                        p.*,
                        (SELECT MAX(pp.fecha_pago) FROM pagos_pensiones pp WHERE pp.pension_id = p.id) AS ultimo_pago_fecha,
                        (SELECT SUM(pp.monto_mxn) FROM pagos_pensiones pp WHERE pp.pension_id = p.id) AS total_pagado
                    FROM pensiones p
                    WHERE p.cliente_nombre LIKE ?
                       OR p.vehiculo_placa LIKE ?
                       OR p.plan_nombre LIKE ?
                    ORDER BY p.esta_activa DESC, p.vigencia_fin DESC, p.id DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$like, $like, $like]);
        } else {
            $sql = "SELECT 
                        p.*,
                        (SELECT MAX(pp.fecha_pago) FROM pagos_pensiones pp WHERE pp.pension_id = p.id) AS ultimo_pago_fecha,
                        (SELECT SUM(pp.monto_mxn) FROM pagos_pensiones pp WHERE pp.pension_id = p.id) AS total_pagado
                    FROM pensiones p
                    ORDER BY p.esta_activa DESC, p.vigencia_fin DESC, p.id DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPension($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM pensiones WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPensionPorPlaca($placa) {
        $stmt = $this->conexion->prepare("SELECT * FROM pensiones WHERE vehiculo_placa = ? LIMIT 1");
        $stmt->execute([strtoupper(trim((string)$placa))]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearPension($datos) {
        $sql = "INSERT INTO pensiones (
                    cliente_nombre, cliente_telefono, vehiculo_placa, vehiculo_tipo,
                    plan_nombre, monto_mxn, vigencia_inicio, vigencia_fin, notas, esta_activa
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            $datos['cliente_nombre'],
            $datos['cliente_telefono'],
            strtoupper(trim($datos['vehiculo_placa'])),
            $datos['vehiculo_tipo'],
            $datos['plan_nombre'],
            $datos['monto_mxn'],
            $datos['vigencia_inicio'],
            $datos['vigencia_fin'],
            $datos['notas'],
            $datos['esta_activa']
        ]);

        return (int)$this->conexion->lastInsertId();
    }

    public function actualizarPension($id, $datos) {
        $sql = "UPDATE pensiones SET
                    cliente_nombre = ?,
                    cliente_telefono = ?,
                    vehiculo_placa = ?,
                    vehiculo_tipo = ?,
                    plan_nombre = ?,
                    monto_mxn = ?,
                    vigencia_inicio = ?,
                    vigencia_fin = ?,
                    notas = ?,
                    esta_activa = ?
                WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['cliente_nombre'],
            $datos['cliente_telefono'],
            strtoupper(trim($datos['vehiculo_placa'])),
            $datos['vehiculo_tipo'],
            $datos['plan_nombre'],
            $datos['monto_mxn'],
            $datos['vigencia_inicio'],
            $datos['vigencia_fin'],
            $datos['notas'],
            $datos['esta_activa'],
            (int)$id
        ]);
    }

    public function eliminarPension($id) {
        $stmt = $this->conexion->prepare("DELETE FROM pensiones WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /* =========================
       VIGENCIA (RENOVACIÓN ACUMULADA)
       ========================= */

    public function renovarVigenciaAcumulando($pensionId, $diasSumar) {
        $pensionId = (int)$pensionId;
        $diasSumar = (int)$diasSumar;

        // Traer vigencia actual
        $stmt = $this->conexion->prepare("SELECT vigencia_inicio, vigencia_fin FROM pensiones WHERE id = ? LIMIT 1");
        $stmt->execute([$pensionId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) return false;

        $hoy = date('Y-m-d');
        $vigFin = $p['vigencia_fin'];

        // Base: si sigue vigente -> desde vigencia_fin, si ya venció -> desde hoy
        $base = ($vigFin >= $hoy) ? $vigFin : $hoy;

        $baseDT = new DateTime($base . " 00:00:00");
        $baseDT->modify("+" . $diasSumar . " days");
        $nuevoFin = $baseDT->format('Y-m-d');

        // Si ya estaba vencida, opcionalmente re-iniciar inicio a hoy
        $nuevoInicio = $p['vigencia_inicio'];
        if ($vigFin < $hoy) $nuevoInicio = $hoy;

        $upd = $this->conexion->prepare("UPDATE pensiones SET vigencia_inicio = ?, vigencia_fin = ? WHERE id = ?");
        return $upd->execute([$nuevoInicio, $nuevoFin, $pensionId]);
    }

    /* =========================
       PAGOS
       ========================= */

    public function registrarPagoPension($pensionId, $pago) {
        $sql = "INSERT INTO pagos_pensiones (
                    pension_id, fecha_pago, monto_mxn, metodo_pago, referencia, notas, usuario
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            (int)$pensionId,
            $pago['fecha_pago'],
            $pago['monto_mxn'],
            $pago['metodo_pago'],
            $pago['referencia'],
            $pago['notas'],
            $pago['usuario']
        ]);
    }

    public function listarPagosPorPension($pensionId) {
        $stmt = $this->conexion->prepare("SELECT * FROM pagos_pensiones WHERE pension_id = ? ORDER BY fecha_pago DESC, id DESC");
        $stmt->execute([(int)$pensionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
