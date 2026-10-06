<?php

declare(strict_types=1);

namespace App\Aplicacion\Configuracion;

use App\Dominio\Avisos\ConexionDeWhatsapp;
use App\Modelos\Negocio;

/**
 * HU-39 · Cierra la sesión de WhatsApp del taller. Desde ese momento los avisos vuelven al envío asistido (RN-40, RN-48).
 */
final readonly class DesconectarWhatsapp
{
    public function __construct(private ConexionDeWhatsapp $conexion) {}

    public function ejecutar(Negocio $negocio): void
    {
        if (($negocio->wa_instancia ?? '') !== '') {
            $this->conexion->desconectar($negocio->wa_instancia);
        }

        $negocio->update([
            'wa_instancia' => null,
            'wa_estado' => 'sin_conectar',
            'wa_numero' => null,
            'wa_conectado_en' => null,
        ]);
    }
}
