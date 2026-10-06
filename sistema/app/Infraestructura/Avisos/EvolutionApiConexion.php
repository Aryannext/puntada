<?php

declare(strict_types=1);

namespace App\Infraestructura\Avisos;

use App\Dominio\Avisos\CodigoDeVinculacion;
use App\Dominio\Avisos\ConexionDeWhatsapp;
use App\Dominio\Avisos\EstadoDeConexion;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Vincula el WhatsApp de un taller con Evolution API (ADR-007). Es la única clase que conoce esos tres caminos:
 * crear la sesión, pedir el código para escanear y preguntar en qué va.
 */
final readonly class EvolutionApiConexion implements ConexionDeWhatsapp
{
    public function __construct(
        private ?string $url,
        private ?string $claveApi,
    ) {}

    public function estaConfigurada(): bool
    {
        return ($this->url ?? '') !== '' && ($this->claveApi ?? '') !== '';
    }

    public function codigoPara(string $instancia): CodigoDeVinculacion
    {
        // Crear una sesión que ya existe responde 403: no es un error, se sigue a pedir el código
        $this->peticion()->post($this->en('/instance/create'), [
            'instanceName' => $instancia,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ]);

        $respuesta = $this->peticion()->get($this->en('/instance/connect/'.rawurlencode($instancia)));
        $imagen = $respuesta->json('base64');

        if (! $respuesta->successful() || ! is_string($imagen) || $imagen === '') {
            throw new RuntimeException("Evolution API no entregó el código de vinculación ({$respuesta->status()}).");
        }

        $escrito = $respuesta->json('pairingCode');

        return new CodigoDeVinculacion($imagen, is_string($escrito) ? $escrito : null);
    }

    public function estadoDe(string $instancia): EstadoDeConexion
    {
        $respuesta = $this->peticion()->get($this->en('/instance/connectionState/'.rawurlencode($instancia)));

        if (! $respuesta->successful()) {
            return EstadoDeConexion::sinConectar();
        }

        // «open» es conectado; «connecting» es que todavía no han escaneado; «close» es que no hay sesión
        if ($respuesta->json('instance.state') !== 'open') {
            return $respuesta->json('instance.state') === 'connecting'
                ? EstadoDeConexion::esperando()
                : EstadoDeConexion::sinConectar();
        }

        $numero = $this->numeroDe($instancia);

        return $numero === null ? EstadoDeConexion::esperando() : EstadoDeConexion::conectado($numero);
    }

    public function desconectar(string $instancia): void
    {
        $this->peticion()->delete($this->en('/instance/logout/'.rawurlencode($instancia)));
        $this->peticion()->delete($this->en('/instance/delete/'.rawurlencode($instancia)));
    }

    /**
     * El número con el que quedó vinculada, que WhatsApp informa como «573001234567@s.whatsapp.net».
     */
    private function numeroDe(string $instancia): ?string
    {
        $respuesta = $this->peticion()->get($this->en('/instance/fetchInstances'), ['instanceName' => $instancia]);
        $instancias = $respuesta->json();
        $datos = is_array($instancias) ? ($instancias[0] ?? $instancias) : [];
        $duenno = is_array($datos) ? ($datos['ownerJid'] ?? null) : null;

        if (! is_string($duenno) || ! preg_match('/^57(3\d{9})@/', $duenno, $partes)) {
            return null;
        }

        return $partes[1];
    }

    private function peticion(): PendingRequest
    {
        return Http::withHeaders(['apikey' => (string) $this->claveApi])
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(20);
    }

    private function en(string $camino): string
    {
        return rtrim((string) $this->url, '/').$camino;
    }
}
