# PM-05 · Pantallas y navegadores

**Verifica:** RNF-05, RNF-07, RNF-09, RNF-11, CA-01.4, CA-10.1, CA-18.2 · **Cuándo:** Sprint 3 sobre las pantallas que existan; completo en el Sprint 4 · **Entorno:** sistema en el VPS con los datos de los mockups

## Objetivo

Comprobar lo que solo se ve en un navegador:

- que las historias Must funcionan en los navegadores del taller;
- que cada pantalla sirve en un celular de 360 px y es accesible;
- que los mensajes de error se entienden;
- tres criterios que dependen del comportamiento de la pantalla.

## Preparación

1. Cargar los datos de los mockups con las fechas corridas a hoy.
2. Tener a mano las [capturas de los mockups](../../03-diseno/mockups/capturas/).
3. Anotar la versión de cada navegador: Chrome para Android en un celular real, y Chrome y Edge de escritorio.

## A · Historias Must en cada navegador (RNF-05)

Recorrer cada historia con los datos de sus criterios. Marcar **Sí** si todos sus criterios se cumplen en ese navegador, o anotar el número del issue del defecto.

RNF-05 pide las dos últimas versiones principales de cada navegador. Se prueba la versión actual; para la anterior, se revisa que el CSS y el JavaScript usados figuren como disponibles de forma amplia en Baseline, la referencia de compatibilidad de los navegadores.

<!-- recorrido:inicio -->

| Historia | Chrome para Android | Chrome | Edge |
| --- | --- | --- | --- |
| **HU-01** Iniciar y cerrar sesión | | | |
| **HU-02** Cambiar mi contraseña | | | |
| **HU-03** Registrar un cliente | | | |
| **HU-04** Buscar un cliente | | | |
| **HU-05** Consultar la ficha de un cliente | | | |
| **HU-06** Corregir los datos de un cliente | | | |
| **HU-07** Registrar una orden con sus prendas | | | |
| **HU-08** Obtener el número de la orden para marcar la bolsa | | | |
| **HU-09** Escribir un tipo de prenda que no está en la lista | | | |
| **HU-12** Corregir la descripción o el precio de una prenda | | | |
| **HU-14** Consultar el detalle de una orden | | | |
| **HU-15** Listar y buscar órdenes | | | |
| **HU-17** Tomar fotos de las prendas | | | |
| **HU-18** Ver las fotos de una orden para reconocer las prendas | | | |
| **HU-20** Actualizar el estado de una prenda | | | |
| **HU-21** Entregar la orden al cliente | | | |
| **HU-22** Cancelar una orden | | | |
| **HU-23** Registrar un pago o abono | | | |
| **HU-25** Anular un pago mal registrado | | | |
| **HU-28** Recibir un aviso cuando mi ropa está lista | | | |
| **HU-29** Enviar con un toque los avisos pendientes | | | |
| **HU-30** No avisar una orden que ya no está lista | | | |
| **HU-31** Consultar los avisos de una orden | | | |
| **HU-32** Ver el panel del día | | | |
| **HU-33** Ver las órdenes atrasadas | | | |
| **HU-34** Ver las órdenes sin reclamar | | | |

<!-- recorrido:fin -->

## B · Cada pantalla a 360 px y su accesibilidad (RNF-07, RNF-11)

Para cada pantalla:

1. **Sin desplazamiento horizontal:** a 360 px de ancho, la página no se mueve hacia los lados.
2. **Controles de 44 × 44 px o más:** botones, enlaces y campos. Se miden con el script de capturas adaptado, que recorre cada pantalla y reporta los elementos más pequeños.
3. **Accesibilidad de Lighthouse de 90 o más**, con el perfil móvil.
4. **Contraste de 4,5:1 o más** en los textos, según el reporte de Lighthouse.
5. **Igual al mockup:** se compara con su captura. Una diferencia se acepta solo si hay un motivo anotado.

<!-- pantallas:inicio -->

| Pantalla | Sin desplazamiento horizontal | Controles de 44 px | Lighthouse | Contraste | Igual al mockup |
| --- | --- | --- | --- | --- | --- |
| **PT-01** Iniciar sesión | | | | | |
| **PT-02** Panel del día | | | | | |
| **PT-03** Clientes | | | | | |
| **PT-04** Registrar o corregir un cliente | | | | | |
| **PT-05** Ficha del cliente | | | | | |
| **PT-06** Nueva orden | | | | | |
| **PT-07** Número para la bolsa | | | | | |
| **PT-08** Órdenes | | | | | |
| **PT-09** Detalle de la orden | | | | | |
| **PT-10** Fotos de la orden | | | | | |
| **PT-11** Acciones de una prenda | | | | | |
| **PT-12** Devolver una prenda sin arreglar | | | | | |
| **PT-13** Corregir una prenda | | | | | |
| **PT-14** Registrar un pago | | | | | |
| **PT-15** Anular un pago | | | | | |
| **PT-16** Entregar la orden | | | | | |
| **PT-17** Cancelar la orden | | | | | |
| **PT-18** Avisos por enviar | | | | | |
| **PT-19** Mensaje en el celular del cliente | | | | | |
| **PT-20** Órdenes atrasadas | | | | | |
| **PT-21** Órdenes sin reclamar | | | | | |
| **PT-22** Dinero | | | | | |
| **PT-23** Ajustes | | | | | |

<!-- pantallas:fin -->

## C · Mensajes de validación (RNF-09)

Provocar cada error de validación de los formularios: campos vacíos, celular inválido, precio cero, fecha pasada, pago mayor que el saldo y plazo fuera de rango. Cada mensaje debe cumplir las cuatro condiciones:

- aparece junto al campo que lo causó;
- está en español;
- dice cómo corregirlo;
- no tiene términos técnicos, como `validation.required`, `null`, `SQLSTATE` o un código de error.

| Formulario | Campo | Mensaje que apareció | Junto al campo | Dice cómo corregir | Sin términos técnicos |
| --- | --- | --- | --- | --- | --- |
| Registrar un cliente (PT-04) | Nombre | Escribe el nombre del cliente. | Sí | Sí | Sí |
| Registrar un cliente (PT-04) | Celular | Escribe el celular del cliente. | Sí | Sí | Sí |
| Registrar un cliente (PT-04) | Celular | Escribe un celular colombiano de 10 dígitos que empiece por 3 | Sí | Sí | Sí |
| Nueva orden (PT-06) | Cliente | Elige el cliente de la orden. | Sí | Sí | Sí |
| Nueva orden (PT-06) | Fecha de entrega acordada | Elige la fecha de entrega acordada. | Sí | Sí | Sí |
| Nueva orden (PT-06) | Tipo de prenda | Elige el tipo de prenda. | Sí | Sí | Sí |
| Nueva orden (PT-06) | Qué arreglo lleva | Escribe qué arreglo lleva la prenda. | Sí | Sí | Sí |
| Nueva orden (PT-06) | Precio | El precio debe ser mayor que cero. | Sí | Sí | Sí |
| Nueva orden (PT-06) | Fecha de entrega acordada | La entrega no puede ser antes de la fecha de recepción. | Sí | Sí | Sí |
| Nueva orden (PT-06) | Precio | Escribe el precio del arreglo. | Sí | Sí | Sí |
| Registrar un pago (PT-14) | Valor | El valor debe ser mayor que cero. | Sí | Sí | Sí |
| Registrar un pago (PT-14) | Efectivo | Elige cómo pagó. | Sí | Sí | Sí |
| Registrar un pago (PT-14) | Valor | El pago no puede superar el saldo pendiente de $21.000 | Sí | Sí | Sí |
| Ajustes · plazo (PT-23) | Una orden lista queda sin reclamar después de | El plazo debe estar entre 1 y 365 días. | Sí | Sí | Sí |
| Ajustes · mi taller (PT-23) | Nombre del taller | Tu taller necesita un nombre. | Sí | Sí | Sí |
| Ajustes · mi taller (PT-23) | Mi nombre | Escribe tu nombre. | Sí | Sí | Sí |
| Ajustes · tipo nuevo (PT-23) | — | Escribe el nombre del tipo de prenda. | **No**, banda al inicio | Sí | Sí |

## D · Criterios que dependen de la pantalla

Probar en los tres navegadores:

| Criterio | Qué se hace | Qué debe pasar | Chrome Android | Chrome | Edge |
| --- | --- | --- | --- | --- | --- |
| **CA-01.4** | Cerrar sesión y tocar Atrás en el navegador | Aparece el inicio de sesión y ningún dato del taller | Sí | Sí | Sí |
| **CA-10.1** | Llenar una orden con dos prendas, registrar un cliente nuevo desde ella y volver | La orden tiene al cliente seleccionado y las dos prendas siguen escritas | No aplica | No aplica | No aplica |
| **CA-18.2** | En las fotos de una orden, tocar una foto | La foto se ve ampliada | Sí | Sí | Sí |

## Criterio de aprobación

- **A:** todas las historias Must pasan en los tres navegadores.
- **B:** todas las pantallas cumplen los cinco puntos.
- **C:** todos los mensajes cumplen las cuatro condiciones.
- **D:** los tres criterios pasan en los tres navegadores.

## Registro

**Fecha:** 21 de septiembre de 2026, ampliada el 4 de octubre · **Commit:** `fc48f20`, y `dddd77f` para las secciones B y C · **Chrome para Android (versión y celular):** no anotada · **Chrome (versión):** no anotada · **Edge (versión):** 154.0.4258.53, usado sin interfaz para medir B y C

Además se probó en **Brave 1.95.104**, que usa el mismo motor que Chrome. Queda como prueba adicional y no reemplaza a ninguno de los tres navegadores.

Las tablas de las secciones A a D son la hoja de registro.

**Resultado:** **Parcial.** Aprobadas B, C y D. Falta la sección A, que necesita un celular real y Edge con interfaz.

- **B · aprobada.** Las 22 pantallas con ruta se midieron a 360 px sobre el sistema con los datos de ejemplo. **Ninguna se desplaza hacia los lados.** Se midieron **215 controles** y ninguno queda por debajo de 44 × 44 px, contando como objetivo la etiqueta que lo envuelve: un radio de 13 px dentro de un botón grande se toca por el botón. **Las 22 obtienen 100 de accesibilidad en Lighthouse** con perfil móvil —el mínimo pedido era 90— y las 22 pasan la revisión de contraste.
- **C · aprobada.** Se provocaron 17 mensajes distintos en los cuatro formularios con validación. Todos están en español, todos dicen cómo corregir y **ninguno tiene términos técnicos**. Uno aparece fuera de su campo, declarado abajo.
- **D · aprobada** el 21 de septiembre. CA-01.4 y CA-18.2 pasan en los tres navegadores y también en Brave. CA-10.1 no aplicaba entonces porque HU-10 no estaba construida; se construyó el 23 de septiembre y su criterio queda cubierto por la prueba automática `test_ca_10_1_cliente_nuevo`, en `RegistrarClienteTest`.
- **A · pendiente.** El recorrido de las 26 historias Must en los tres navegadores necesita un celular real con Chrome para Android y Edge con interfaz. Lo ejecuta el aprendiz.

Cómo se midió B y C, para poder repetirlo: el sistema se levanta en `127.0.0.1:8012` con los datos de `taller_local`, se entra con la cuenta de desarrollo y se recorre cada pantalla con Edge sin interfaz, por su protocolo de depuración, a 360 px. El tamaño de los controles y el desplazamiento se leen del propio documento; la accesibilidad y el contraste salen de Lighthouse 12.8.2 con perfil móvil.

**Salvedades declaradas:**

- **PT-01, el enlace a la política de tratamiento de datos** mide 282 × 38,3 px: le faltan seis píxeles de alto. Va dentro de una frase, y la norma WCAG 2.2 (criterio 2.5.8) exime a los enlaces en línea, cuyo alto lo fija la altura de la línea del texto que los rodea. No se corrige: agrandarlo rompería el párrafo.
- **El mensaje de «Agregar un tipo» sale como banda al inicio de la sección**, no junto al campo. El motivo es que los ocho formularios de esa sección usan el mismo nombre de campo, `nombre`: el de agregar y el de renombrar cada tipo. Con la clave compartida, el sistema no puede saber cuál de los ocho lo causó, y ponerlo junto a uno lo pondría junto al equivocado. La banda está a la vista, en español y dice cómo corregir.
- **«Igual al mockup» queda pendiente** para las 22 pantallas. Las capturas del sistema a 360 px están tomadas; la comparación con los mockups es a ojo y se hace junto con la sección A.

**Defectos abiertos:** ninguno. Se encontró y se corrigió uno: en el detalle de la orden, las miniaturas no llevaban a las fotos, y el único camino era un enlace pequeño en el título que pasó desapercibido. Corregido en `fc48f20`, también en el mockup.
