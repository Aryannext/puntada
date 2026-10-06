<?php

namespace Tests\Feature\Configuracion;

use App\Aplicacion\Avisos\EnviarAviso;
use App\Modelos\Aviso;
use App\Modelos\Cliente;
use App\Modelos\Negocio;
use App\Modelos\Orden;
use App\Modelos\Prenda;
use App\Modelos\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * HU-39 · El WhatsApp con el que avisa cada taller (RF-44, RN-48). Hasta el 6 de octubre el canal era uno solo para
 * todo el sistema: los avisos de cualquier negocio salían del número de quien instaló.
 */
class ConectarWhatsappTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $duena;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.evolution.url' => 'https://pasarela.local', 'services.evolution.clave_api' => 'clave-de-prueba']);
        $this->duena = Usuario::factory()->create(['nombre' => 'Inés']);
        $this->duena->negocio->update(['nombre' => 'Modistería Inés']);
        $this->fijarReloj('2026-10-06 10:00:00');
    }

    public function test_ca_39_1_conectar_mi_whatsapp(): void
    {
        Http::fake([
            '*/instance/create' => Http::response(['instance' => ['instanceName' => 'taller-1']]),
            '*/instance/connect/*' => Http::response(['base64' => 'data:image/png;base64,UUU=', 'pairingCode' => 'ABCD1234']),
        ]);
        $this->actingAs($this->duena);

        $this->get(route('ajustes'))->assertOk()->assertSee('Conectar mi WhatsApp');

        $this->post(route('ajustes.whatsapp.conectar'))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('ajustes'));

        // Queda esperando el escaneo, con su propia sesión: la de este negocio y de ningún otro (RN-01)
        $negocio = $this->duena->negocio->fresh();
        $this->assertSame(['taller-'.$negocio->id, 'esperando', null], [$negocio->wa_instancia, $negocio->wa_estado, $negocio->wa_numero]);

        // Y el código se ve, con el paso a paso de cómo escanearlo
        Http::fake(fn () => Http::response(['instance' => ['state' => 'connecting']]));
        $this->get(route('ajustes'))
            ->assertOk()
            ->assertSee('Escanea este código con tu WhatsApp')
            ->assertSee('Dispositivos vinculados')
            ->assertSee('data:image/png;base64,UUU=', false)
            ->assertSee('ABCD1234');
    }

    public function test_ca_39_2_ya_quedo_conectado(): void
    {
        $this->duena->negocio->update(['wa_instancia' => 'taller-1', 'wa_estado' => 'esperando']);
        Http::fake([
            '*/instance/connectionState/*' => Http::response(['instance' => ['state' => 'open']]),
            '*/instance/fetchInstances*' => Http::response([['name' => 'taller-1', 'ownerJid' => '573208345460@s.whatsapp.net']]),
        ]);
        $this->actingAs($this->duena);

        $this->get(route('ajustes'))
            ->assertOk()
            ->assertSee('Conectado')
            ->assertSee('3208345460')
            ->assertSee('Desconectar mi WhatsApp');

        $negocio = $this->duena->negocio->fresh();
        $this->assertSame(['conectado', '3208345460'], [$negocio->wa_estado, $negocio->wa_numero]);
        $this->assertNotNull($negocio->wa_conectado_en);
    }

    public function test_ca_39_3_el_aviso_sale_de_mi_numero(): void
    {
        $this->duena->negocio->update([
            'wa_instancia' => 'taller-1', 'wa_estado' => 'conectado',
            'wa_numero' => '3208345460', 'wa_conectado_en' => '2026-10-06 09:00:00',
        ]);
        $aviso = $this->avisoDeMarta();
        Http::fake(['*/message/sendText/*' => Http::response(['key' => ['id' => 'WAMSG-1']])]);

        EnviarAviso::dispatch($aviso->id);
        $this->procesarLaCola();

        $this->assertSame(['enviado', 'evolution_api'], [$aviso->fresh()->estado, $aviso->fresh()->canal]);
        // Salió por la sesión de este taller, no por otra
        Http::assertSent(fn (Request $peticion) => str_contains($peticion->url(), '/message/sendText/taller-1'));
    }

    public function test_ca_39_4_sin_conectar_lo_envio_yo(): void
    {
        $aviso = $this->avisoDeMarta();
        Http::fake();

        EnviarAviso::dispatch($aviso->id);
        $this->procesarLaCola();

        // Sin WhatsApp conectado no se envía solo: queda para que lo mande la dueña (RN-40)
        $this->assertSame('pendiente_asistido', $aviso->fresh()->estado);
        Http::assertNothingSent();

        $this->actingAs($this->duena);
        $this->get(route('avisos.pendientes'))->assertOk()->assertSee('Marta');
    }

    public function test_ca_39_5_desconectar(): void
    {
        $this->duena->negocio->update([
            'wa_instancia' => 'taller-1', 'wa_estado' => 'conectado',
            'wa_numero' => '3208345460', 'wa_conectado_en' => '2026-10-06 09:00:00',
        ]);
        Http::fake();
        $this->actingAs($this->duena);

        $this->delete(route('ajustes.whatsapp.desconectar'))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('ajustes'));

        $negocio = $this->duena->negocio->fresh();
        $this->assertSame(['sin_conectar', null, null], [$negocio->wa_estado, $negocio->wa_instancia, $negocio->wa_numero]);
        Http::assertSent(fn (Request $peticion) => str_contains($peticion->url(), '/instance/logout/taller-1'));

        // Y el siguiente aviso ya no sale solo
        $aviso = $this->avisoDeMarta();
        Http::fake();
        EnviarAviso::dispatch($aviso->id);
        $this->procesarLaCola();
        $this->assertSame('pendiente_asistido', $aviso->fresh()->estado);
    }

    private function avisoDeMarta(?Negocio $negocio = null): Aviso
    {
        $negocio ??= $this->duena->negocio;
        $marta = Cliente::factory()->create(['negocio_id' => $negocio->id, 'nombre' => 'Marta Rincón', 'celular' => '3104567890']);
        $orden = Orden::factory()->create([
            'cliente_id' => $marta->id, 'numero' => 42, 'fecha_entrega_acordada' => '2026-10-08',
            'recibida_en' => '2026-10-01 09:00:00', 'lista_en' => '2026-10-06 09:30:00',
        ]);
        Prenda::factory()->create(['orden_id' => $orden->id, 'precio' => 15000, 'estado' => 'terminada']);

        return Aviso::factory()->create([
            'orden_id' => $orden->id, 'ciclo_lista_en' => '2026-10-06 09:30:00',
            'estado' => 'en_cola', 'canal' => null, 'mensaje' => null, 'resuelto_en' => null,
        ]);
    }

    private function procesarLaCola(): void
    {
        Artisan::call('queue:work', ['--queue' => 'avisos', '--stop-when-empty' => true, '--sleep' => 0, '--memory' => 1024]);
    }
}
