<?php

namespace Tests\Soporte;

use App\Dominio\Avisos\CanalDeAviso;
use App\Dominio\Avisos\CanalesDeAviso;

/**
 * Doble de prueba de CanalesDeEvolutionApi (RN-48): entrega el canal del negocio que lo pide y anota qué sesión se pidió.
 * Un negocio sin sesión conectada recibe un canal no disponible, igual que en producción.
 */
final class CanalesDeAvisoFalso implements CanalesDeAviso
{
    /** @var list<string|null> */
    public array $instanciasPedidas = [];

    public function __construct(private readonly CanalDeAvisoFalso $canal) {}

    public function paraInstancia(?string $instancia): CanalDeAviso
    {
        $this->instanciasPedidas[] = $instancia;

        return ($instancia ?? '') === '' ? CanalDeAvisoFalso::sinConfigurar() : $this->canal;
    }
}
