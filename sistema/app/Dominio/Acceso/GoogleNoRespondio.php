<?php

declare(strict_types=1);

namespace App\Dominio\Acceso;

use RuntimeException;

/**
 * No se pudo hablar con Google: sin internet, tiempo agotado o su servicio caído. No es que la identidad sea inválida,
 * así que no se trata como un correo sin acceso (RN-45): se le dice a la usuaria que vuelva a intentar o entre con su contraseña.
 */
final class GoogleNoRespondio extends RuntimeException
{
    public const MENSAJE = 'No pudimos conectar con Google. Intenta otra vez, o entra con tu usuario y contraseña.';

    public function __construct()
    {
        parent::__construct(self::MENSAJE);
    }
}
