<?php

declare(strict_types=1);

namespace App\Dominio\Avisos;

/**
 * El código que la dueña escanea con su celular para vincular el WhatsApp del taller (HU-39).
 * Lleva la imagen lista para mostrar y, por si la cámara falla, el código de ocho caracteres que WhatsApp
 * también acepta escrito a mano.
 */
final readonly class CodigoDeVinculacion
{
    public function __construct(
        public string $imagen,
        public ?string $codigoEscrito = null,
    ) {}
}
