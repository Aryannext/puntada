@extends('plantilla')

@section('titulo', 'Ajustes')

@section('contenido')
  <header class="barra">
    <a class="boton-icono" href="{{ route('panel') }}" aria-label="Volver"><i class="i i-atras"></i></a>
    <h1>Ajustes</h1>
  </header>

  <main class="contenido">
    @if (session('exito'))
      <div class="banda banda-exito" role="status"><i class="i i-check"></i><span>{{ session('exito') }}</span></div>
    @endif

    {{-- HU-38: el nombre del taller se ve aquí y les llega a los clientes en cada aviso (RN-46) --}}
    <section class="seccion">
      <h2 class="titulo-seccion">Mi taller</h2>
      <form class="tarjeta pila" method="POST" action="{{ route('ajustes.taller') }}">
        @csrf
        @method('PUT')
        <div @class(['campo', 'con-error' => $errors->has('nombre_negocio')])>
          <label for="nombre_negocio">Nombre del taller</label>
          <input class="entrada" id="nombre_negocio" name="nombre_negocio" maxlength="120" required
            value="{{ old('nombre_negocio', $negocio) }}"
            @error('nombre_negocio') aria-invalid="true" aria-describedby="error-nombre_negocio" @enderror>
          @error('nombre_negocio')
            <span class="mensaje-error" id="error-nombre_negocio"><i class="i i-alerta"></i>{{ $message }}</span>
          @else
            <span class="ayuda">Así te nombran los avisos que reciben tus clientes.</span>
          @enderror
        </div>
        <div @class(['campo', 'con-error' => $errors->has('nombre_usuaria')])>
          <label for="nombre_usuaria">Mi nombre</label>
          <input class="entrada" id="nombre_usuaria" name="nombre_usuaria" maxlength="120" required
            value="{{ old('nombre_usuaria', $usuaria) }}"
            @error('nombre_usuaria') aria-invalid="true" aria-describedby="error-nombre_usuaria" @enderror>
          @error('nombre_usuaria')
            <span class="mensaje-error" id="error-nombre_usuaria"><i class="i i-alerta"></i>{{ $message }}</span>
          @else
            <span class="ayuda">Con este nombre te saluda la pantalla Hoy.</span>
          @enderror
        </div>
        <button class="btn btn-secundario" type="submit">Guardar</button>
      </form>
    </section>
    {{-- HU-39: el WhatsApp con el que avisa este taller. Sin él, los avisos quedan para que los envíe ella (RN-48) --}}
    <section class="seccion">
      <h2 class="titulo-seccion">WhatsApp del taller</h2>
      <div class="tarjeta pila">
        @if ($whatsapp->wa_estado === 'conectado')
          <p><span class="chip chip-conectado">Conectado</span></p>
          <p class="fuerte">Tus avisos salen del {{ $whatsapp->wa_numero }}</p>
          <span class="ayuda">Vinculado el {{ $whatsapp->wa_conectado_en?->translatedFormat('j \d\e F \d\e Y') }}.</span>
          <form method="POST" action="{{ route('ajustes.whatsapp.desconectar') }}">
            @csrf
            @method('DELETE')
            <button class="btn btn-peligro-borde" type="submit">Desconectar mi WhatsApp</button>
          </form>
        @elseif ($codigo)
          <p class="fuerte">Escanea este código con tu WhatsApp</p>
          <ol class="instrucciones">
            <li>Abre WhatsApp en tu celular.</li>
            <li>Toca los tres puntos y entra a <strong>Dispositivos vinculados</strong>.</li>
            <li>Toca <strong>Vincular un dispositivo</strong> y apunta la cámara aquí.</li>
          </ol>
          <img class="codigo-qr" src="{{ $codigo['imagen'] }}" alt="Código para vincular tu WhatsApp" width="264" height="264">
          @if ($codigo['escrito'])
            <span class="ayuda">Si la cámara no lo lee, en WhatsApp elige «Vincular con número de teléfono» y escribe <strong class="codigo">{{ $codigo['escrito'] }}</strong>.</span>
          @endif
          <a class="btn btn-secundario" href="{{ route('ajustes') }}">Ya lo escaneé</a>
        @else
          <p>Mientras no conectes tu WhatsApp, los avisos de orden lista no salen solos: quedan en <a href="{{ route('avisos.pendientes') }}">Avisos por enviar</a> para que los mandes tú con un toque.</p>
          <span class="ayuda">Al conectarlo, el aviso le llega al cliente desde el número de tu taller. Se vincula escaneando un código, como WhatsApp Web, y puedes desconectarlo cuando quieras.</span>
          <form method="POST" action="{{ route('ajustes.whatsapp.conectar') }}">
            @csrf
            <button class="btn btn-secundario" type="submit">Conectar mi WhatsApp</button>
          </form>
        @endif
      </div>
    </section>

    <section class="seccion">
      <h2 class="titulo-seccion">Contraseña</h2>
      <form class="tarjeta" method="POST" action="{{ route('ajustes.contrasena') }}">
        @csrf
        @method('PUT')
        <x-campo nombre="contrasena_actual" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" required />
        <x-campo nombre="contrasena_nueva" etiqueta="Nueva contraseña" tipo="password" ayuda="Al menos 8 caracteres." autocomplete="new-password" required />
        <x-campo nombre="contrasena_nueva_confirmation" etiqueta="Repite la nueva contraseña" tipo="password" autocomplete="new-password" required />
        <button class="btn btn-secundario" type="submit"><i class="i i-candado"></i>Cambiar contraseña</button>
      </form>
    </section>

    {{-- HU-35: el plazo tras el cual una orden lista se considera sin reclamar (RN-35) --}}
    <section class="seccion">
      <h2 class="titulo-seccion">Órdenes sin reclamar</h2>
      <form class="tarjeta pila" method="POST" action="{{ route('ajustes.plazo') }}">
        @csrf
        @method('PUT')
        <div @class(['campo', 'con-error' => $errors->has('dias_sin_reclamar')])>
          <label for="dias_sin_reclamar">Una orden lista queda sin reclamar después de</label>
          <div class="entre">
            <input class="entrada entrada-corta" id="dias_sin_reclamar" name="dias_sin_reclamar" inputmode="numeric"
              value="{{ old('dias_sin_reclamar', $plazo) }}" required
              @error('dias_sin_reclamar') aria-invalid="true" aria-describedby="error-dias_sin_reclamar" @enderror>
            <span class="fuerte crece">días</span>
          </div>
          @error('dias_sin_reclamar')
            {{-- CA-35.2 --}}
            <span class="mensaje-error" id="error-dias_sin_reclamar"><i class="i i-alerta"></i>{{ $message }}</span>
          @else
            <span class="ayuda">Entre 1 y 365 días.</span>
          @enderror
        </div>
        <button class="btn btn-secundario" type="submit">Guardar plazo</button>
      </form>
    </section>

    {{-- HU-16: la lista de tipos es propia del negocio y crece con lo que se escribe en «Otro» (RN-43) --}}
    <section class="seccion">
      <h2 class="titulo-seccion">Tipos de prenda</h2>
      @error('nombre')
        <div class="banda banda-error" role="alert"><i class="i i-alerta"></i><span>{{ $message }}</span></div>
      @enderror
      <ul class="lista">
        @foreach ($tipos as $tipo)
          <li class="fila alinear-arriba">
            <form class="pila pila-junta crece" method="POST" action="{{ route('ajustes.tipos.renombrar', $tipo) }}">
              @csrf
              @method('PUT')
              <label class="sr" for="tipo-{{ $tipo->id }}">Nombre del tipo de prenda</label>
              <input class="entrada" id="tipo-{{ $tipo->id }}" name="nombre" maxlength="60" required
                value="{{ $tipo->nombre }}" @class(['texto-2' => ! $tipo->activo])>
              @unless ($tipo->activo)
                <span class="detalle">No aparece al registrar prendas nuevas</span>
              @endunless
              <button class="btn btn-texto btn-pequeno alinear-inicio" type="submit">Guardar nombre</button>
            </form>
            <form method="POST" action="{{ route('ajustes.tipos.activo', $tipo) }}">
              @csrf
              @method('PUT')
              <input type="hidden" name="activo" value="{{ $tipo->activo ? '0' : '1' }}">
              <button class="btn btn-texto btn-pequeno" type="submit">{{ $tipo->activo ? 'Dejar de usar' : 'Volver a usar' }}</button>
            </form>
          </li>
        @endforeach
      </ul>
      {{-- CA-16.3: agregar uno sin tener que registrar una prenda con «Otro» --}}
      <form class="tarjeta pila pila-junta" method="POST" action="{{ route('ajustes.tipos.agregar') }}">
        @csrf
        <label class="etiqueta" for="tipo-nuevo">Agregar un tipo</label>
        <div class="fila-campo">
          <input class="entrada crece" id="tipo-nuevo" name="nombre" maxlength="60" required placeholder="Overol, sudadera, cortina…">
          <button class="btn btn-secundario btn-pequeno" type="submit"><i class="i i-mas i-sm"></i>Agregar</button>
        </div>
      </form>

      <p class="texto-2 pequeno">Al dejar de usar un tipo, las prendas que ya lo tienen lo conservan; solo deja de ofrecerse al registrar.</p>
    </section>

    <form method="POST" action="{{ route('sesion.salir') }}">
      @csrf
      <button class="btn btn-peligro-borde" type="submit"><i class="i i-salir"></i>Cerrar sesión</button>
    </form>
  </main>
@endsection
