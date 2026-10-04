// Portal del proyecto Puntada: el buscador y los diagramas. Sin librerías más que Mermaid.
// Funciona abriendo los archivos desde una carpeta, sin servidor: el índice llega como un script (indice.js), no con fetch.

(function () {
  const raiz = document.documentElement.dataset.raiz || '';

  // --- Buscador: por código («RN-22», «rn22») o por palabras del título, sin importar tildes ---
  const entrada = document.querySelector('[data-buscar]');
  const lista = document.querySelector('[data-resultados]');
  const normalizar = (texto) => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  const sinGuion = (texto) => normalizar(texto).replace(/[-\s.]/g, '');
  const indice = (window.INDICE || []).map(([codigo, titulo, tipo, url]) => ({
    codigo, titulo, tipo, url, clave: sinGuion(codigo), texto: normalizar(`${codigo} ${titulo}`),
  }));

  let activo = -1;
  const pintar = (encontrados) => {
    lista.innerHTML = '';
    activo = -1;
    encontrados.slice(0, 40).forEach((e) => {
      const li = document.createElement('li');
      const a = document.createElement('a');
      a.href = raiz + e.url;
      a.innerHTML = `<strong></strong><span></span><span class="tipo"></span>`;
      a.children[0].textContent = e.codigo;
      a.children[1].textContent = e.titulo;
      a.children[2].textContent = e.tipo;
      li.append(a);
      lista.append(li);
    });
    lista.hidden = encontrados.length === 0;
  };

  entrada?.addEventListener('input', () => {
    const consulta = entrada.value.trim();
    if (consulta === '') {
      lista.hidden = true;
      return;
    }
    const clave = sinGuion(consulta);
    const palabras = normalizar(consulta).split(/\s+/);
    const exactos = indice.filter((e) => e.clave === clave);
    const porCodigo = indice.filter((e) => e.clave.startsWith(clave) && e.clave !== clave);
    const porTexto = indice.filter((e) => !e.clave.startsWith(clave) && palabras.every((p) => e.texto.includes(p)));
    pintar([...exactos, ...porCodigo, ...porTexto]);
  });

  entrada?.addEventListener('keydown', (evento) => {
    const enlaces = [...lista.querySelectorAll('a')];
    if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
      evento.preventDefault();
      activo = (activo + (evento.key === 'ArrowDown' ? 1 : -1) + enlaces.length) % enlaces.length;
      enlaces.forEach((a, i) => a.classList.toggle('activo', i === activo));
      enlaces[activo]?.scrollIntoView({ block: 'nearest' });
    } else if (evento.key === 'Enter' && enlaces.length) {
      window.location.href = (enlaces[activo] || enlaces[0]).href;
    } else if (evento.key === 'Escape') {
      lista.hidden = true;
    }
  });

  document.addEventListener('click', (evento) => {
    if (!evento.target.closest('.buscador')) {
      lista && (lista.hidden = true);
    }
  });

  // «/» lleva al buscador desde cualquier parte, como en GitHub
  document.addEventListener('keydown', (evento) => {
    if (evento.key === '/' && document.activeElement !== entrada) {
      evento.preventDefault();
      entrada?.focus();
    }
  });


  // --- Filtro de las listas largas: la de criterios tiene 134 filas ---
  const filtro = document.querySelector("[data-filtro]");
  const tabla = document.querySelector("[data-lista]");
  if (filtro && tabla) {
    const filas = Array.from(tabla.tBodies[0].rows);
    const marcador = document.querySelector("[data-marcador]");
    const vacio = document.querySelector("[data-vacio]");
    const textos = filas.map((f) => f.textContent.toLowerCase());
    filtro.addEventListener("input", () => {
      const busca = filtro.value.trim().toLowerCase();
      let visibles = 0;
      filas.forEach((fila, i) => {
        const pasa = !busca || textos[i].includes(busca);
        fila.hidden = !pasa;
        if (pasa) visibles++;
      });
      if (marcador) marcador.textContent = `${visibles} de ${filas.length}`;
      if (vacio) vacio.hidden = visibles > 0;
    });
  }

  // --- Diagramas: Mermaid los dibuja en la página, con los mismos textos del documento ---
  if (window.mermaid && document.querySelector('pre.mermaid')) {
    // Los de secuencia no se encogen al ancho de la página: con muchos participantes, la letra quedaba ilegible
    window.mermaid.initialize({
      startOnLoad: false, securityLevel: 'loose', theme: 'neutral',
      flowchart: { htmlLabels: true }, sequence: { useMaxWidth: false },
    });
    window.mermaid.run({ querySelector: 'pre.mermaid' });
  }
})();
