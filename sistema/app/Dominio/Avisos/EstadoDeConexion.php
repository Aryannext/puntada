<?php

declare(strict_types=1);

namespace App\Dominio\Avisos;

/**
 * En qué va la vinculación del WhatsApp de un taller (HU-39, RN-48).
 */
final readonly class EstadoDeConexion
{
    public const SIN_CONECTAR = 'sin_conectar';

    public const ESPERANDO = 'esperando';

    public const CONECTADO = 'conectado';

    public function __construct(
        public string $estado,
        public ?string $numero = null,
    ) {}

    public static function conectado(string $numero): self
    {
        return new self(self::CONECTADO, $numero);
    }

    public static function esperando(): self
    {
        return new self(self::ESPERANDO);
    }

    public static function sinConectar(): self
    {
        return new self(self::SIN_CONECTAR);
    }

    public function estaConectado(): bool
    {
        return $this->estado === self::CONECTADO;
    }
}
