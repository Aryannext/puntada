<?php

declare(strict_types=1);

namespace App\Aplicacion\Configuracion;

use App\Dominio\Avisos\ConexionDeWhatsapp;
use App\Dominio\Avisos\EstadoDeConexion;
use App\Dominio\Compartido\Reloj;
use App\Modelos\Negocio;

/**
 * HU-39 · Pregunta a WhatsApp en qué va la vinculación y lo guarda en el negocio (RN-48).
 * Lo consulta la pantalla de Ajustes mientras la dueña escanea, y así también se entera de una sesión caída.
 */
final readonly class EstadoDeWhatsapp
{
    public function __construct(
        private ConexionDeWhatsapp $conexion,
        private Reloj $reloj,
    ) {}

    public function ejecutar(Negocio $negocio): EstadoDeConexion
    {
        if (($negocio->wa_instancia ?? '') === '') {
            return EstadoDeConexion::sinConectar();
        }

        $estado = $this->conexion->estadoDe($negocio->wa_instancia);

        $negocio->update($estado->estaConectado()
            ? ['wa_estado' => 'conectado', 'wa_numero' => $estado->numero, 'wa_conectado_en' => $this->reloj->ahora()]
            : ['wa_estado' => $estado->estado, 'wa_numero' => null, 'wa_conectado_en' => null]);

        return $estado;
    }
}
