<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Negocio extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'negocios';

    protected $fillable = ['nombre', 'dias_sin_reclamar', 'wa_instancia', 'wa_numero', 'wa_estado', 'wa_conectado_en'];

    protected function casts(): array
    {
        return ['dias_sin_reclamar' => 'integer', 'wa_conectado_en' => 'datetime'];
    }

    /**
     * La sesión de WhatsApp con la que este negocio avisa, o null si no tiene ninguna conectada (RN-48).
     * Mientras no esté conectada, sus avisos van al envío asistido.
     */
    public function instanciaConectada(): ?string
    {
        return $this->wa_estado === 'conectado' ? $this->wa_instancia : null;
    }

    /**
     * @return HasMany<Usuario, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }

    /**
     * @return HasMany<Cliente, $this>
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    /**
     * @return HasMany<TipoPrenda, $this>
     */
    public function tiposPrenda(): HasMany
    {
        return $this->hasMany(TipoPrenda::class);
    }

    /**
     * @return HasMany<MetodoPago, $this>
     */
    public function metodosPago(): HasMany
    {
        return $this->hasMany(MetodoPago::class);
    }

    /**
     * @return HasMany<Orden, $this>
     */
    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class);
    }
}
