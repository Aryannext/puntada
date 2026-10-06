<?php

declare(strict_types=1);

namespace App\Dominio\Avisos;

/**
 * Con qué canal avisa cada negocio (RN-48). Antes había un solo canal para todo el sistema, configurado al instalar:
 * los avisos de cualquier taller salían del mismo número. Ahora el canal se pide por negocio, y un negocio sin
 * WhatsApp conectado recibe un canal no disponible, para que su aviso quede en envío asistido (RN-40).
 */
interface CanalesDeAviso
{
    /**
     * @param  string|null  $instancia  La sesión de WhatsApp conectada del negocio, o null si no tiene ninguna.
     */
    public function paraInstancia(?string $instancia): CanalDeAviso;
}
