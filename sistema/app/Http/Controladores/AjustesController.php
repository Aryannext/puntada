<?php

namespace App\Http\Controladores;

use App\Aplicacion\Configuracion\CambiarContrasena;
use App\Aplicacion\Configuracion\CambiarPlazoSinReclamar;
use App\Aplicacion\Configuracion\ConectarWhatsapp;
use App\Aplicacion\Configuracion\DesconectarWhatsapp;
use App\Aplicacion\Configuracion\EstadoDeWhatsapp;
use App\Aplicacion\Configuracion\GestionarTiposDePrenda;
use App\Aplicacion\Configuracion\PersonalizarTaller;
use App\Http\Solicitudes\AjustesRequest;
use App\Http\Solicitudes\ContrasenaRequest;
use App\Modelos\TipoPrenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PT-23 · Ajustes: la contraseña (HU-02), el plazo sin reclamar (HU-35), los tipos de prenda (HU-16),
 * el nombre del taller (HU-38) y el WhatsApp con el que avisa (HU-39).
 */
class AjustesController
{
    public function mostrar(Request $solicitud, GestionarTiposDePrenda $tiposDePrenda, EstadoDeWhatsapp $estadoDeWhatsapp): View
    {
        $negocio = $solicitud->user()->negocio;

        // Si quedó esperando el escaneo, al abrir Ajustes se vuelve a preguntar: así también se ve una sesión caída
        if ($negocio->wa_estado === 'esperando') {
            $estadoDeWhatsapp->ejecutar($negocio);
            $negocio->refresh();
        }

        return view('pantallas.pt-23-ajustes', [
            'negocio' => $negocio->nombre,
            'usuaria' => $solicitud->user()->nombre,
            'plazo' => $negocio->dias_sin_reclamar,
            'tipos' => $tiposDePrenda->todos(),
            'whatsapp' => $negocio,
            'codigo' => session('codigo_de_whatsapp'),
        ]);
    }

    /**
     * HU-39 · Prepara la sesión del taller y deja el código para escanear (RN-48).
     */
    public function conectarWhatsapp(Request $solicitud, ConectarWhatsapp $conectar): RedirectResponse
    {
        $codigo = $conectar->ejecutar($solicitud->user()->negocio);

        // En la sesión van datos planos, no el objeto: lo que se guarda ahí se serializa
        return redirect()->route('ajustes')->with('codigo_de_whatsapp', [
            'imagen' => $codigo->imagen,
            'escrito' => $codigo->codigoEscrito,
        ]);
    }

    /**
     * HU-39 · En qué va la vinculación. La pantalla lo consulta mientras la dueña escanea.
     */
    public function estadoDeWhatsapp(Request $solicitud, EstadoDeWhatsapp $estadoDeWhatsapp): JsonResponse
    {
        $estado = $estadoDeWhatsapp->ejecutar($solicitud->user()->negocio);

        return response()->json(['estado' => $estado->estado, 'numero' => $estado->numero]);
    }

    /**
     * HU-39 · Cierra la sesión: desde aquí los avisos vuelven al envío asistido (RN-40).
     */
    public function desconectarWhatsapp(Request $solicitud, DesconectarWhatsapp $desconectar): RedirectResponse
    {
        $desconectar->ejecutar($solicitud->user()->negocio);

        return redirect()->route('ajustes')->with('exito', 'Listo: el sistema ya no usa tu WhatsApp. Los avisos quedarán para que los envíes tú.');
    }

    /**
     * HU-38 · El nombre del taller y el de la usuaria: los ve ella en el saludo y sus clientes en cada aviso.
     */
    public function personalizar(AjustesRequest $solicitud, PersonalizarTaller $personalizar): RedirectResponse
    {
        $personalizar->ejecutar(
            $solicitud->user(),
            $solicitud->validated('nombre_negocio'),
            $solicitud->validated('nombre_usuaria'),
        );

        return redirect()->route('ajustes')->with('exito', 'Listo: así se llama tu taller ahora.');
    }

    /**
     * HU-16 · Agregar un tipo de prenda sin tener que registrar una prenda con «Otro» (CA-16.3).
     */
    public function agregarTipo(AjustesRequest $solicitud, GestionarTiposDePrenda $tiposDePrenda): RedirectResponse
    {
        $tipo = $tiposDePrenda->agregar((int) $solicitud->user()->negocio_id, $solicitud->validated('nombre'));

        return redirect()->route('ajustes')->with('exito', "«{$tipo->nombre}» ya aparece al registrar prendas.");
    }

    /**
     * HU-35 · Cambiar después de cuántos días una orden lista se considera sin reclamar (RN-35).
     */
    public function cambiarPlazo(AjustesRequest $solicitud, CambiarPlazoSinReclamar $cambiarPlazo): RedirectResponse
    {
        $cambiarPlazo->ejecutar($solicitud->user()->negocio, (int) $solicitud->validated('dias_sin_reclamar'));

        return redirect()->route('ajustes')
            ->with('exito', 'El plazo quedó en '.$solicitud->validated('dias_sin_reclamar').' días.');
    }

    /**
     * HU-16 · Renombrar un tipo de prenda: las prendas que ya lo usan muestran el nombre nuevo (CA-16.1).
     */
    public function renombrarTipo(AjustesRequest $solicitud, TipoPrenda $tipo, GestionarTiposDePrenda $tiposDePrenda): RedirectResponse
    {
        $tiposDePrenda->renombrar($tipo, $solicitud->validated('nombre'));

        return redirect()->route('ajustes')->with('exito', "El tipo de prenda ahora se llama «{$tipo->nombre}».");
    }

    /**
     * HU-16 · Dejar de usar un tipo, o volver a usarlo. Las prendas que ya lo tienen lo conservan (CA-16.2).
     */
    public function cambiarActivoTipo(Request $solicitud, TipoPrenda $tipo, GestionarTiposDePrenda $tiposDePrenda): RedirectResponse
    {
        $activo = $solicitud->boolean('activo');
        $tiposDePrenda->cambiarActivo($tipo, $activo);

        return redirect()->route('ajustes')->with(
            'exito',
            $activo
                ? "«{$tipo->nombre}» vuelve a aparecer al registrar prendas."
                : "«{$tipo->nombre}» ya no aparece al registrar prendas nuevas."
        );
    }

    public function cambiarContrasena(ContrasenaRequest $solicitud, CambiarContrasena $cambiarContrasena): RedirectResponse
    {
        $cambiarContrasena->ejecutar($solicitud->user(), $solicitud->validated('contrasena_nueva'));

        return redirect()->route('ajustes')
            ->with('exito', 'Tu contraseña cambió. Se cerró la sesión en los demás dispositivos.');
    }
}
