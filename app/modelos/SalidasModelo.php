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
                ORDER BY id DESC
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$placa]);
        return $stmt->fetch();
    }

    // =========================
    // 2) INGRESOS ACTIVOS
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
                    t.costo_boleto_perdido,
                    t.extra_noche,
                    t.tolerancia_entrada_minutos,
                    i.pago_adelantado_monto,
                    i.pago_adelantado_concepto,
                    i.pago_adelantado_nota,
                    i.pago_adelantado_fecha,
                    i.pago_adelantado_usuario
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

    public function obtenerIngresoActivoPorId($id)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

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
                    t.costo_boleto_perdido,
                    t.extra_noche,
                    t.tolerancia_entrada_minutos,
                    i.pago_adelantado_monto,
                    i.pago_adelantado_concepto,
                    i.pago_adelantado_nota,
                    i.pago_adelantado_fecha,
                    i.pago_adelantado_usuario
                FROM ingresos_vehiculos i
                INNER JOIN tarifas_vehiculos t ON t.id = i.id_tarifa
                WHERE i.id = ?
                  AND i.estado = 'En Estacionamiento'
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // =========================
    // 3) CÁLCULO COBRO
    // =========================
    // - Cobra solo minutos dentro del horario operativo (apertura -> cierre)
    // - Si la salida es posterior al cierre del día de la salida, aplica extra_noche
    // - Si hubo al menos 1 extra_noche, aplica gracia configurable post-apertura (tarifa.tolerancia_entrada_minutos)
    // =========================
    public function calcularCobro(
        $fecha_ingreso,
        $fecha_salida,
        $costo_hora,
        $costo_fraccion_extra,
        $tolerancia_extra_minutos,
        $extra_noche_monto = 0.00,
        $tolerancia_entrada_minutos = 0
    ) {
        $entrada = new DateTime($fecha_ingreso);
        $salida  = new DateTime($fecha_salida);

        if ($salida <= $entrada) {
            return [
                'minutos_estancia' => 0,
                'minutos_cobrables' => 0,
                'minutos_totales' => 0,
                'monto_tiempo' => 0.00,
                'extra_noche' => 0.00,
                'monto_total' => 0.00,
                'hora_apertura' => null,
                'hora_cierre' => null,
                'sale_despues_cierre' => 0,
                'cobro_hasta' => null,
                'gracia_aplicada_minutos' => 0
            ];
        }

        $diffEst = $entrada->diff($salida);
        $minutos_estancia = ($diffEst->days * 24 * 60) + ($diffEst->h * 60) + $diffEst->i;

        $minutos_cobrables = 0;

        $extra_noche_veces = 0;
        $cobro_hasta = null;
        $hora_apertura = null;
        $hora_cierre = null;
        $sale_despues_cierre = 0;

        $gracia_aplicada_minutos = 0;

        $d = (clone $entrada);
        $d->setTime(0, 0, 0);
        $ultimoDia = (clone $salida);
        $ultimoDia->setTime(0, 0, 0);

        while ($d <= $ultimoDia) {
            $horario = $this->obtenerHorarioOperacionPorFecha($d);

            if (
                !$horario ||
                (int)$horario['esta_abierto'] !== 1 ||
                empty($horario['hora_apertura']) ||
                empty($horario['hora_cierre'])
            ) {
                $d->modify('+1 day');
                continue;
            }

            $open = (clone $d);
            $close = (clone $d);

            [$oh, $om, $os] = array_map('intval', explode(':', $horario['hora_apertura']));
            [$ch, $cm, $cs] = array_map('intval', explode(':', $horario['hora_cierre']));

            $open->setTime($oh, $om, $os);
            $close->setTime($ch, $cm, $cs);

            if ($close <= $open) {
                $close->modify('+1 day');
            }

            if ($salida > $open && $entrada < $close) {
                if ($cobro_hasta === null || $close > $cobro_hasta) {
                    $cobro_hasta = (clone $close);
                    $hora_apertura = $horario['hora_apertura'];
                    $hora_cierre = $horario['hora_cierre'];
                }
            }

            if ($close > $entrada && $close < $salida) {
                $extra_noche_veces += 1;
            }

            $inicio = ($entrada > $open) ? $entrada : $open;
            $fin    = ($salida  < $close) ? $salida  : $close;

            if ($fin > $inicio) {
                $di = $inicio->diff($fin);
                $minutosDia = ($di->days * 24 * 60) + ($di->h * 60) + $di->i;
                $minutos_cobrables += $minutosDia;

                // Gracia configurable después de apertura (solo si hay al menos 1 extra_noche y es el día de salida)
                if ((int)$tolerancia_entrada_minutos > 0) {
                    $diaSalida = (clone $salida);
                    $diaSalida->setTime(0, 0, 0);

                    if ($extra_noche_veces > 0 && $d->format('Y-m-d') === $diaSalida->format('Y-m-d')) {
                        $inicioVentanaDia = (clone $open);
                        if ($salida > $inicioVentanaDia) {
                            $hasta = ($salida < $close) ? $salida : $close;
                            if ($hasta > $inicioVentanaDia) {
                                $diG = $inicioVentanaDia->diff($hasta);
                                $minDiaDesdeApertura = ($diG->days * 24 * 60) + ($diG->h * 60) + $diG->i;

                                $g = min((int)$tolerancia_entrada_minutos, (int)$minDiaDesdeApertura, (int)$minutosDia);
                                if ($g > 0) {
                                    $minutos_cobrables = max(0, (int)$minutos_cobrables - (int)$g);
                                    $gracia_aplicada_minutos += (int)$g;
                                }
                            }
                        }
                    }
                }
            }

            $d->modify('+1 day');
        }

        $tolerancia = (int)$tolerancia_extra_minutos;
        $costo_hora = (float)$costo_hora;
        $costo_fraccion = (float)$costo_fraccion_extra;
        $monto_tiempo = 0.00;

        if ($minutos_cobrables <= $tolerancia) {
            $monto_tiempo = 0.00;
        } else {
            if ($minutos_cobrables < 60) {
                $monto_tiempo = $costo_hora;
            } else {
                $horas_completas = (int)floor($minutos_cobrables / 60);
                $minutos_restantes = $minutos_cobrables % 60;

                $monto_tiempo = $horas_completas * $costo_hora;

                if ($minutos_restantes > 0 && $minutos_restantes > $tolerancia) {
                    $monto_tiempo += $costo_fraccion;
                }
            }
        }

        $extra_noche = 0.00;
        if ($extra_noche_veces > 0) {
            $extra_noche = (float)$extra_noche_monto * (int)$extra_noche_veces;
            $sale_despues_cierre = 1;
        }

        $monto_total = round(((float)$monto_tiempo + (float)$extra_noche), 2);

        return [
            'minutos_estancia' => (int)$minutos_estancia,
            'minutos_cobrables' => (int)$minutos_cobrables,
            'minutos_totales' => (int)$minutos_cobrables,
            'monto_tiempo' => round((float)$monto_tiempo, 2),
            'extra_noche' => round((float)$extra_noche, 2),
            'extra_noche_veces' => (int)$extra_noche_veces,
            'monto_total' => (float)$monto_total,
            'hora_apertura' => $hora_apertura,
            'hora_cierre' => $hora_cierre,
            'sale_despues_cierre' => (int)$sale_despues_cierre,
            'cobro_hasta' => ($cobro_hasta instanceof DateTime) ? $cobro_hasta->format('Y-m-d H:i:s') : null,
            'gracia_aplicada_minutos' => (int)$gracia_aplicada_minutos
        ];
    }

    private function obtenerHorarioOperacionPorFecha(DateTime $dt)
    {
        $map = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $dia = $map[(int)$dt->format('N')] ?? 'Lunes';

        $sql = "SELECT esta_abierto, hora_apertura, hora_cierre
                FROM horarios_operacion
                WHERE dia_semana = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$dia]);
        return $stmt->fetch();
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
        $extra_noche,
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
                (id_ingreso, fecha_salida, minutos_totales, monto_total, extra_noche, monto_recibido, monto_cambio, usuario_cobro, boleto_perdido,
                 descuento_tipo, descuento_valor, descuento_monto, descuento_motivo)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtSalida = $this->conexion->prepare($sqlSalida);
        $okSalida = $stmtSalida->execute([
            (int)$id_ingreso,
            $fecha_salida,
            (int)$minutos_totales,
            (float)$monto_total,
            (float)$extra_noche,
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
