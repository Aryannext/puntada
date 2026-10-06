<?php

declare(strict_types=1);

namespace App\Infraestructura\Avisos;

use App\Dominio\Avisos\CanalDeAviso;
use App\Dominio\Avisos\CanalesDeAviso;

/**
 * Arma el canal de cada negocio con su propia sesión de WhatsApp (RN-48). Un negocio sin sesión conectada recibe
 * un canal sin instancia, que no está disponible: su aviso queda para el envío asistido (RN-40).
 */
final readonly class CanalesDeEvolutionApi implements CanalesDeAviso
{
    public function __construct(
        private ?string $url,
        private ?string $claveApi,
    ) {}

    public function paraInstancia(?string $instancia): CanalDeAviso
    {
        return new EvolutionApiCanal($this->url, $this->claveApi, $instancia);
    }
}
