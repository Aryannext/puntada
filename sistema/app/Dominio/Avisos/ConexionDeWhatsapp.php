<?php

declare(strict_types=1);

namespace App\Dominio\Avisos;

/**
 * Vincular el WhatsApp de un taller (HU-39, RN-48). WhatsApp no deja enviar como un número que alguien escriba:
 * el número se vincula escaneando un código desde su propio celular, como en WhatsApp Web. Por eso esto no recibe
 * un número, sino que entrega un código y después informa con cuál quedó conectado.
 */
interface ConexionDeWhatsapp
{
    /**
     * Prepara la sesión y devuelve el código para escanear. Si ya existía, devuelve uno nuevo.
     */
    public function codigoPara(string $instancia): CodigoDeVinculacion;

    /**
     * En qué va la vinculación, y con qué número quedó si ya está conectada.
     */
    public function estadoDe(string $instancia): EstadoDeConexion;

    /**
     * Cierra la sesión del taller. Deja de enviar por ese número.
     */
    public function desconectar(string $instancia): void;
}
