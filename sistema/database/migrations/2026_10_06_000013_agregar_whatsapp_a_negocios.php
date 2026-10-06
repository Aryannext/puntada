<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * HU-39 · El WhatsApp de cada taller. Hasta ahora el canal era uno solo para todo el sistema, configurado al
 * instalar: los avisos salían del número de quien instaló. Cada negocio conecta el suyo y envía desde ahí (RN-48).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->string('wa_instancia', 60)->nullable()->after('dias_sin_reclamar')
                ->comment('Nombre de la sesión de WhatsApp de este negocio en la pasarela; única en todo el sistema (RN-48)');
            $table->char('wa_numero', 10)->nullable()->after('wa_instancia')
                ->comment('Número de WhatsApp que quedó conectado, como lo informa WhatsApp al vincular (RN-48)');
            $table->enum('wa_estado', ['sin_conectar', 'esperando', 'conectado'])->default('sin_conectar')->after('wa_numero')
                ->comment('En qué va la conexión del WhatsApp del negocio (RN-48)');
            $table->dateTime('wa_conectado_en')->nullable()->after('wa_estado')
                ->comment('Fecha y hora en que quedó conectado');
            $table->unique('wa_instancia', 'uq_negocios_wa_instancia');
        });

        DB::statement("ALTER TABLE negocios
            ADD CONSTRAINT ck_negocios_wa_numero CHECK (wa_numero IS NULL OR REGEXP_LIKE(wa_numero, '^3[0-9]{9}$')),
            ADD CONSTRAINT ck_negocios_wa_conectado CHECK ((wa_estado = 'conectado') = (wa_numero IS NOT NULL AND wa_conectado_en IS NOT NULL))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE negocios
            DROP CONSTRAINT ck_negocios_wa_conectado,
            DROP CONSTRAINT ck_negocios_wa_numero');

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropUnique('uq_negocios_wa_instancia');
            $table->dropColumn(['wa_instancia', 'wa_numero', 'wa_estado', 'wa_conectado_en']);
        });
    }
};
