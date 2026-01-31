<?php
class SalidasModelo
{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion = $db;
    }

    // =========================
    // 1) PENSIONES (PRIMERO)
    // =========================
    public function obtenerPensionVigentePorPlaca($placa)
    {
        $placa = strtoupper(trim((string)$placa));
        if ($placa === '') return false;

        $sql = "SELECT
                    id,
                    cliente_nombre,
                    cliente_telefono,
                    vehiculo_placa,
                    vehiculo_tipo,
                    plan_nombre,
                    monto_mxn,
                    vigencia_inicio,
                    vigencia_fin,
                    esta_activa
                FROM pensiones
                WHERE vehiculo_placa = ?
                  AND esta_activa = 1
                  AND CURDATE() BETWEEN vigencia_inicio AND vigencia_fin
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$placa]);
        return $stmt->fetch();
    }

    // =========================
    // 2) INGRESOS (SI NO HAY PENSION)
    // =========================
    public function obtenerIngresoActivoPorPlaca($placa)
    {
        $placa = strtoupper(trim((string)$placa));
        if ($placa === '') return false;

        $sql = "SELECT 
                    i.id,
                    i.placa,
                    i.id_tarifa,
                    i.marca,
                    i.color,
                    i.fecha_ingreso,
                    i.estado,
                    i.usuario_registro,
                    t.tipo_vehiculo,
                    t.costo_hora,
                    t.costo_fraccion_extra,
                    t.tolerancia_extra_minutos,
                    t.costo_boleto_perdido
                FROM ingresos_vehiculos i
                INNER JOIN tarifas_vehiculos t ON t.id = i.id_tarifa
                WHERE i.placa = ?
                  AND i.estado = 'En Estacionamiento'
                ORDER BY i.id DESC
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$placa]);
        return $stmt->fetch();
    }

    public function obtenerIngresoActivoPorId($id_ingreso)
    {
        $id_ingreso = (int)$id_ingreso;
        if ($id_ingreso <= 0) return false;

        $sql = "SELECT 
                    i.id,
                    i.placa,
                    i.id_tarifa,
                    i.marca,
                    i.color,
                    i.fecha_ingreso,
                    i.estado,
                    i.usuario_registro,
                    t.tipo_vehiculo,
                    t.costo_hora,
                    t.costo_fraccion_extra,
                    t.tolerancia_extra_minutos,
                    t.costo_boleto_perdido
                FROM ingresos_vehiculos i
                INNER JOIN tarifas_vehiculos t ON t.id = i.id_tarifa
                WHERE i.id = ?
                  AND i.estado = 'En Estacionamiento'
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([(int)$id_ingreso]);
        return $stmt->fetch();
    }

    // =========================
    // Cálculo estándar (para ingresos)
    // =========================
    public function calcularCobro($fecha_ingreso, $fecha_salida, $costo_hora, $costo_fraccion_extra, $tolerancia_extra_minutos)
    {
        $entrada = new DateTime($fecha_ingreso);
        $salida  = new DateTime($fecha_salida);

        $diff = $entrada->diff($salida);
        $minutos_totales = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        $tolerancia = (int)$tolerancia_extra_minutos;
        $costo_hora = (float)$costo_hora;
        $costo_fraccion = (float)$costo_fraccion_extra;

        $monto_total = 0.00;

        if ($minutos_totales <= $tolerancia) {
            $monto_total = 0.00;
        } else {
            if ($minutos_totales < 60) {
                $monto_total = $costo_hora;
            } else {
                $horas_completas = (int)floor($minutos_totales / 60);
                $minutos_restantes = $minutos_totales % 60;

                $monto_total = $horas_completas * $costo_hora;

                if ($minutos_restantes > 0 && $minutos_restantes > $tolerancia) {
                    $monto_total += $costo_fraccion;
                }
            }
        }

        return [
            'minutos_totales' => $minutos_totales,
            'monto_total' => round($monto_total, 2)
        ];
    }

    // =========================
    // Salidas (solo INGRESOS)
    // =========================
    public function existeSalidaPorIngreso($id_ingreso)
    {
        $sql = "SELECT id FROM salidas_vehiculos WHERE id_ingreso = ? LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([(int)$id_ingreso]);
        return $stmt->fetch();
    }

    public function registrarSalidaIngreso(
        $id_ingreso,
        $fecha_salida,
        $minutos_totales,
        $monto_total,
        $monto_recibido,
        $monto_cambio,
        $usuario_cobro = null,
        $boleto_perdido = 0,
        $descuento_tipo = null,
        $descuento_valor = null,
        $descuento_monto = 0.00,
        $descuento_motivo = null
    ) {
        $sqlSalida = "INSERT INTO salidas_vehiculos
                (id_ingreso, fecha_salida, minutos_totales, monto_total, monto_recibido, monto_cambio, usuario_cobro, boleto_perdido,
                 descuento_tipo, descuento_valor, descuento_monto, descuento_motivo)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtSalida = $this->conexion->prepare($sqlSalida);
        $okSalida = $stmtSalida->execute([
            (int)$id_ingreso,
            $fecha_salida,
            (int)$minutos_totales,
            (float)$monto_total,
            (float)$monto_recibido,
            (float)$monto_cambio,
            $usuario_cobro,
            (int)($boleto_perdido ? 1 : 0),
            $descuento_tipo,
            ($descuento_valor === null ? null : (float)$descuento_valor),
            (float)$descuento_monto,
            $descuento_motivo
        ]);

        if (!$okSalida) return false;

        $sqlFinalizar = "UPDATE ingresos_vehiculos
                         SET estado = 'Finalizado'
                         WHERE id = ?
                           AND estado = 'En Estacionamiento'";
        $stmtFinalizar = $this->conexion->prepare($sqlFinalizar);
        $stmtFinalizar->execute([(int)$id_ingreso]);

        return ($stmtFinalizar->rowCount() > 0);
    }
}
