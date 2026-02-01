<?php
class ReportesModelo {
    private $conexion;

    public function __construct($db) {
        $this->conexion = $db;
    }

    private function buildWhereSalidas($usuario, $metodo, &$params) {
        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta ";
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($metodo !== '') {
            $where .= " AND s.metodo_pago = :metodo ";
            $params[':metodo'] = $metodo;
        }
        return $where;
    }

    public function obtenerUsuariosActivos() {
        $stmt = $this->conexion->prepare("
            SELECT id, nombre, usuario, rol
            FROM usuarios
            WHERE activo = 1
            ORDER BY rol ASC, nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // CAJA: Corte por cajero
    // =========================
    public function corteCajeroResumen($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = $this->buildWhereSalidas($usuario, $metodo, $params);

        $sql = "
            SELECT
                COUNT(*) AS salidas,
                COALESCE(SUM(s.monto_total),0) AS total_vendido,
                COALESCE(SUM(s.monto_recibido),0) AS total_recibido,
                COALESCE(SUM(s.monto_cambio),0) AS total_cambio,
                COALESCE(SUM(s.descuento_monto),0) AS total_descuentos,
                COALESCE(SUM(s.extra_noche),0) AS total_extra_noche,
                COALESCE(SUM(CASE WHEN s.boleto_perdido = 1 THEN 1 ELSE 0 END),0) AS boletos_perdidos
            FROM salidas_vehiculos s
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function corteCajeroPorMetodo($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = $this->buildWhereSalidas($usuario, $metodo, $params);

        $sql = "
            SELECT
                s.metodo_pago,
                COALESCE(SUM(s.monto_total),0) AS total
            FROM salidas_vehiculos s
            $where
            GROUP BY s.metodo_pago
            ORDER BY s.metodo_pago ASC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // normaliza llaves esperadas por UI
        $out = ['Efectivo' => 0, 'Tarjeta' => 0, 'Transferencia' => 0, 'Otro' => 0];
        foreach ($rows as $r) {
            $k = $r['metodo_pago'] ?? '';
            if ($k !== '' && array_key_exists($k, $out)) $out[$k] = (float)$r['total'];
        }
        return $out;
    }

    public function corteCajeroDetalle($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = $this->buildWhereSalidas($usuario, $metodo, $params);

        $sql = "
            SELECT
                s.id AS salida_id,
                s.fecha_salida,
                i.placa,
                t.tipo_vehiculo,
                s.usuario_cobro,
                s.metodo_pago,
                s.referencia_pago,
                s.minutos_totales,
                s.monto_total,
                s.descuento_monto,
                s.descuento_tipo,
                s.descuento_valor,
                s.descuento_motivo,
                s.extra_noche,
                s.monto_recibido,
                s.monto_cambio,
                s.boleto_perdido
            FROM salidas_vehiculos s
            INNER JOIN ingresos_vehiculos i ON i.id = s.id_ingreso
            INNER JOIN tarifas_vehiculos t ON t.id = i.id_tarifa
            $where
            ORDER BY s.fecha_salida DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // CAJA: Corte diario (resumen por día)
    // =========================
    public function corteDiarioResumen($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = $this->buildWhereSalidas($usuario, $metodo, $params);

        $sql = "
            SELECT
                DATE(s.fecha_salida) AS dia,
                COUNT(*) AS salidas,
                COALESCE(SUM(s.monto_total),0) AS total_vendido,
                COALESCE(SUM(s.descuento_monto),0) AS total_descuentos,
                COALESCE(SUM(s.extra_noche),0) AS total_extra_noche,
                COALESCE(AVG(s.minutos_totales),0) AS minutos_promedio,
                COALESCE(SUM(CASE WHEN s.boleto_perdido = 1 THEN 1 ELSE 0 END),0) AS boletos_perdidos
            FROM salidas_vehiculos s
            $where
            GROUP BY DATE(s.fecha_salida)
            ORDER BY dia DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // CAJA: Descuentos
    // =========================
    public function descuentosResumen($desde, $hasta, $usuario = '', $tipo = '', $min_monto = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta AND s.descuento_monto > 0 ";
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($tipo !== '') {
            $where .= " AND s.descuento_tipo = :tipo ";
            $params[':tipo'] = $tipo;
        }
        if ($min_monto !== '' && is_numeric($min_monto)) {
            $where .= " AND s.descuento_monto >= :min_monto ";
            $params[':min_monto'] = (float)$min_monto;
        }

        $sql = "
            SELECT
                COUNT(*) AS movimientos,
                COALESCE(SUM(s.descuento_monto),0) AS total_descuentos,
                COALESCE(AVG(s.descuento_monto),0) AS promedio_descuento
            FROM salidas_vehiculos s
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function descuentosDetalle($desde, $hasta, $usuario = '', $tipo = '', $min_monto = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta AND s.descuento_monto > 0 ";
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($tipo !== '') {
            $where .= " AND s.descuento_tipo = :tipo ";
            $params[':tipo'] = $tipo;
        }
        if ($min_monto !== '' && is_numeric($min_monto)) {
            $where .= " AND s.descuento_monto >= :min_monto ";
            $params[':min_monto'] = (float)$min_monto;
        }

        $sql = "
            SELECT
                s.id AS salida_id,
                s.fecha_salida,
                i.placa,
                s.usuario_cobro,
                s.descuento_tipo,
                s.descuento_valor,
                s.descuento_monto,
                s.descuento_motivo,
                s.monto_total
            FROM salidas_vehiculos s
            INNER JOIN ingresos_vehiculos i ON i.id = s.id_ingreso
            $where
            ORDER BY s.fecha_salida DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // CAJA: Boletos perdidos
    // =========================
    public function boletosPerdidosResumen($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta AND s.boleto_perdido = 1 ";
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($metodo !== '') {
            $where .= " AND s.metodo_pago = :metodo ";
            $params[':metodo'] = $metodo;
        }

        $sql = "
            SELECT
                COUNT(*) AS movimientos,
                COALESCE(SUM(s.monto_total),0) AS total_cobrado
            FROM salidas_vehiculos s
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function boletosPerdidosDetalle($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta AND s.boleto_perdido = 1 ";
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($metodo !== '') {
            $where .= " AND s.metodo_pago = :metodo ";
            $params[':metodo'] = $metodo;
        }

        $sql = "
            SELECT
                s.id AS salida_id,
                s.fecha_salida,
                i.placa,
                s.usuario_cobro,
                s.metodo_pago,
                s.referencia_pago,
                s.monto_total,
                s.monto_recibido,
                s.monto_cambio
            FROM salidas_vehiculos s
            INNER JOIN ingresos_vehiculos i ON i.id = s.id_ingreso
            $where
            ORDER BY s.fecha_salida DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // CAJA: Extra noche
    // =========================
    public function extraNocheResumen($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta AND s.extra_noche > 0 ";
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($metodo !== '') {
            $where .= " AND s.metodo_pago = :metodo ";
            $params[':metodo'] = $metodo;
        }

        $sql = "
            SELECT
                COUNT(*) AS movimientos,
                COALESCE(SUM(s.extra_noche),0) AS total_extra_noche,
                COALESCE(SUM(s.monto_total),0) AS total_vendido_relacionado
            FROM salidas_vehiculos s
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function extraNocheDetalle($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta AND s.extra_noche > 0 ";
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($metodo !== '') {
            $where .= " AND s.metodo_pago = :metodo ";
            $params[':metodo'] = $metodo;
        }

        $sql = "
            SELECT
                s.id AS salida_id,
                s.fecha_salida,
                i.placa,
                s.usuario_cobro,
                s.metodo_pago,
                s.referencia_pago,
                s.extra_noche,
                s.monto_total
            FROM salidas_vehiculos s
            INNER JOIN ingresos_vehiculos i ON i.id = s.id_ingreso
            $where
            ORDER BY s.fecha_salida DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // CAJA: Anticipos (pagos adelantados)
    // =========================
    public function anticiposResumen($desde, $hasta, $usuario = '', $concepto = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE i.pago_adelantado_fecha BETWEEN :desde AND :hasta AND i.pago_adelantado_monto > 0 ";
        if ($usuario !== '') {
            $where .= " AND i.pago_adelantado_usuario = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($concepto !== '') {
            $where .= " AND i.pago_adelantado_concepto LIKE :concepto ";
            $params[':concepto'] = '%' . $concepto . '%';
        }

        $sql = "
            SELECT
                COUNT(*) AS movimientos,
                COALESCE(SUM(i.pago_adelantado_monto),0) AS total_anticipos
            FROM ingresos_vehiculos i
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function anticiposDetalle($desde, $hasta, $usuario = '', $concepto = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];

        $where = " WHERE i.pago_adelantado_fecha BETWEEN :desde AND :hasta AND i.pago_adelantado_monto > 0 ";
        if ($usuario !== '') {
            $where .= " AND i.pago_adelantado_usuario = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($concepto !== '') {
            $where .= " AND i.pago_adelantado_concepto LIKE :concepto ";
            $params[':concepto'] = '%' . $concepto . '%';
        }

        $sql = "
            SELECT
                i.id AS ingreso_id,
                i.placa,
                i.fecha_ingreso,
                i.pago_adelantado_monto,
                i.pago_adelantado_concepto,
                i.pago_adelantado_nota,
                i.pago_adelantado_fecha,
                i.pago_adelantado_usuario
            FROM ingresos_vehiculos i
            $where
            ORDER BY i.pago_adelantado_fecha DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // OPERACIÓN: Ocupación
    // =========================
    public function ocupacionResumen($placa = '', $id_tarifa = 0) {
        $params = [];
        $where = " WHERE i.estado = 'En Estacionamiento' ";
        if ($placa !== '') {
            $where .= " AND i.placa LIKE :placa ";
            $params[':placa'] = '%' . $placa . '%';
        }
        if ((int)$id_tarifa > 0) {
            $where .= " AND i.id_tarifa = :id_tarifa ";
            $params[':id_tarifa'] = (int)$id_tarifa;
        }

        $sql = "
            SELECT
                COUNT(*) AS vehiculos_dentro
            FROM ingresos_vehiculos i
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function ocupacionDetalle($placa = '', $id_tarifa = 0) {
        $params = [];
        $where = " WHERE i.estado = 'En Estacionamiento' ";
        if ($placa !== '') {
            $where .= " AND i.placa LIKE :placa ";
            $params[':placa'] = '%' . $placa . '%';
        }
        if ((int)$id_tarifa > 0) {
            $where .= " AND i.id_tarifa = :id_tarifa ";
            $params[':id_tarifa'] = (int)$id_tarifa;
        }

        $sql = "
            SELECT
                i.id AS ingreso_id,
                i.placa,
                t.tipo_vehiculo,
                i.marca,
                i.color,
                i.fecha_ingreso,
                i.usuario_registro,
                i.pago_adelantado_monto,
                i.pago_adelantado_concepto
            FROM ingresos_vehiculos i
            INNER JOIN tarifas_vehiculos t ON t.id = i.id_tarifa
            $where
            ORDER BY i.fecha_ingreso DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // OPERACIÓN: Entradas por periodo
    // =========================
    public function entradasPorDia($desde, $hasta, $id_tarifa = 0, $usuario = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = " WHERE i.fecha_ingreso BETWEEN :desde AND :hasta ";

        if ((int)$id_tarifa > 0) {
            $where .= " AND i.id_tarifa = :id_tarifa ";
            $params[':id_tarifa'] = (int)$id_tarifa;
        }
        if ($usuario !== '') {
            $where .= " AND i.usuario_registro = :usuario ";
            $params[':usuario'] = $usuario;
        }

        $sql = "
            SELECT
                DATE(i.fecha_ingreso) AS dia,
                COUNT(*) AS entradas
            FROM ingresos_vehiculos i
            $where
            GROUP BY DATE(i.fecha_ingreso)
            ORDER BY dia DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function entradasDetalle($desde, $hasta, $id_tarifa = 0, $usuario = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = " WHERE i.fecha_ingreso BETWEEN :desde AND :hasta ";

        if ((int)$id_tarifa > 0) {
            $where .= " AND i.id_tarifa = :id_tarifa ";
            $params[':id_tarifa'] = (int)$id_tarifa;
        }
        if ($usuario !== '') {
            $where .= " AND i.usuario_registro = :usuario ";
            $params[':usuario'] = $usuario;
        }

        $sql = "
            SELECT
                i.id AS ingreso_id,
                i.fecha_ingreso,
                i.placa,
                t.tipo_vehiculo,
                i.estado,
                i.usuario_registro
            FROM ingresos_vehiculos i
            INNER JOIN tarifas_vehiculos t ON t.id = i.id_tarifa
            $where
            ORDER BY i.fecha_ingreso DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // OPERACIÓN: Estancia promedio
    // =========================
    public function estanciaPromedioResumen($desde, $hasta, $id_tarifa = 0, $usuario = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta ";

        if ((int)$id_tarifa > 0) {
            $where .= " AND i.id_tarifa = :id_tarifa ";
            $params[':id_tarifa'] = (int)$id_tarifa;
        }
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }

        $sql = "
            SELECT
                COUNT(*) AS salidas,
                COALESCE(AVG(s.minutos_totales),0) AS minutos_promedio,
                COALESCE(MAX(s.minutos_totales),0) AS max_minutos,
                COALESCE(MIN(s.minutos_totales),0) AS min_minutos
            FROM salidas_vehiculos s
            INNER JOIN ingresos_vehiculos i ON i.id = s.id_ingreso
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function estanciasMasLargas($desde, $hasta, $id_tarifa = 0, $usuario = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = " WHERE s.fecha_salida BETWEEN :desde AND :hasta ";

        if ((int)$id_tarifa > 0) {
            $where .= " AND i.id_tarifa = :id_tarifa ";
            $params[':id_tarifa'] = (int)$id_tarifa;
        }
        if ($usuario !== '') {
            $where .= " AND s.usuario_cobro = :usuario ";
            $params[':usuario'] = $usuario;
        }

        $sql = "
            SELECT
                s.fecha_salida,
                i.placa,
                t.tipo_vehiculo,
                s.usuario_cobro,
                s.minutos_totales,
                s.monto_total
            FROM salidas_vehiculos s
            INNER JOIN ingresos_vehiculos i ON i.id = s.id_ingreso
            INNER JOIN tarifas_vehiculos t ON t.id = i.id_tarifa
            $where
            ORDER BY s.minutos_totales DESC
            LIMIT 50
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // PENSIONES
    // =========================
    public function pensionesActivas($q = '') {
        $params = [];
        $where = " WHERE p.esta_activa = 1 ";
        if ($q !== '') {
            $where .= " AND (p.cliente_nombre LIKE :q OR p.vehiculo_placa LIKE :q OR p.cliente_telefono LIKE :q) ";
            $params[':q'] = '%' . $q . '%';
        }

        $sql = "
            SELECT
                p.id,
                p.cliente_nombre,
                p.cliente_telefono,
                p.vehiculo_placa,
                p.vehiculo_tipo,
                p.plan_nombre,
                p.monto_mxn,
                p.vigencia_inicio,
                p.vigencia_fin,
                p.notas
            FROM pensiones p
            $where
            ORDER BY p.vigencia_fin ASC, p.cliente_nombre ASC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pensionesPorVencer($dias = 7) {
        $dias = (int)$dias;
        if ($dias <= 0) $dias = 7;

        $sql = "
            SELECT
                p.id,
                p.cliente_nombre,
                p.cliente_telefono,
                p.vehiculo_placa,
                p.vehiculo_tipo,
                p.plan_nombre,
                p.monto_mxn,
                p.vigencia_inicio,
                p.vigencia_fin,
                DATEDIFF(p.vigencia_fin, CURDATE()) AS dias_restantes
            FROM pensiones p
            WHERE p.esta_activa = 1
              AND p.vigencia_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
            ORDER BY p.vigencia_fin ASC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':dias' => $dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pagosPensionesResumen($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = " WHERE pp.fecha_pago BETWEEN :desde AND :hasta ";

        if ($usuario !== '') {
            $where .= " AND pp.usuario = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($metodo !== '') {
            $where .= " AND pp.metodo_pago = :metodo ";
            $params[':metodo'] = $metodo;
        }

        $sql = "
            SELECT
                COUNT(*) AS pagos,
                COALESCE(SUM(pp.monto_mxn),0) AS total_cobrado,
                COALESCE(AVG(pp.monto_mxn),0) AS ticket_promedio
            FROM pagos_pensiones pp
            $where
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function pagosPensionesDetalle($desde, $hasta, $usuario = '', $metodo = '') {
        $params = [':desde' => $desde, ':hasta' => $hasta];
        $where = " WHERE pp.fecha_pago BETWEEN :desde AND :hasta ";

        if ($usuario !== '') {
            $where .= " AND pp.usuario = :usuario ";
            $params[':usuario'] = $usuario;
        }
        if ($metodo !== '') {
            $where .= " AND pp.metodo_pago = :metodo ";
            $params[':metodo'] = $metodo;
        }

        $sql = "
            SELECT
                pp.id AS pago_id,
                pp.fecha_pago,
                pp.monto_mxn,
                pp.metodo_pago,
                pp.referencia,
                pp.usuario,
                pp.plan_nombre,
                p.cliente_nombre,
                p.vehiculo_placa,
                p.vigencia_inicio,
                p.vigencia_fin
            FROM pagos_pensiones pp
            INNER JOIN pensiones p ON p.id = pp.pension_id
            $where
            ORDER BY pp.fecha_pago DESC
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
