<?php

declare(strict_types=1);

namespace App\Aplicacion\Configuracion;

use App\Dominio\Avisos\CodigoDeVinculacion;
use App\Dominio\Avisos\ConexionDeWhatsapp;
use App\Modelos\Negocio;

/**
 * HU-39 · Prepara la sesión de WhatsApp del taller y entrega el código para escanear (RN-48).
 * La sesión se nombra con el negocio, así que dos talleres nunca comparten la misma (RN-01).
 */
final readonly class ConectarWhatsapp
{
    public function __construct(private ConexionDeWhatsapp $conexion) {}

    public function ejecutar(Negocio $negocio): CodigoDeVinculacion
    {
        $instancia = $negocio->wa_instancia ?: 'taller-'.$negocio->id;
        $codigo = $this->conexion->codigoPara($instancia);

        // Queda esperando hasta que alguien escanee: mientras tanto sus avisos siguen yendo al envío asistido
        $negocio->update([
            'wa_instancia' => $instancia,
            'wa_estado' => 'esperando',
            'wa_numero' => null,
            'wa_conectado_en' => null,
        ]);

        return $codigo;
    }
}
