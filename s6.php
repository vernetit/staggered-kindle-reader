<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Staggered text layout txt to read in Kindle</title>

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: #111;
      color: #eee;
      font-family: "Courier New", Courier, monospace;
    }

    .panel {
      width: min(680px, calc(100vw - 24px));
      display: grid;
      gap: 14px;
      padding: 20px;
      border: 1px solid #333;
      border-radius: 18px;
      background: #1b1b1b;
      box-shadow: 0 12px 40px rgba(0,0,0,0.35);
    }

    label {
      display: grid;
      gap: 6px;
      font-size: 14px;
      color: #bbb;
    }

    select,
    button,
    input {
      width: 100%;
      font: inherit;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #444;
      background: #222;
      color: #eee;
    }

    input[type="checkbox"] {
      width: auto;
      transform: scale(1.25);
    }

    button {
      cursor: pointer;
      font-weight: 800;
      color: #7dd3fc;
    }

    button:hover {
      filter: brightness(1.15);
    }

    .status {
      min-height: 1.4em;
      font-size: 13px;
      color: #aaa;
    }

    .configBox {
      display: grid;
      gap: 12px;
      padding: 14px;
      border: 1px solid #333;
      border-radius: 14px;
      background: #161616;
    }

    .configTitle {
      font-weight: 800;
      color: #7dd3fc;
      margin-bottom: 4px;
    }

    .grid2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .checkLabel {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .columnsBox {
      display: grid;
      gap: 10px;
    }

    .columnConfig {
      display: grid;
      grid-template-columns: 80px 1fr 1fr;
      gap: 10px;
      align-items: end;
      padding: 10px;
      border: 1px solid #303030;
      border-radius: 12px;
      background: #1f1f1f;
    }

    .columnTitle {
      font-weight: 800;
      color: #facc15;
      padding-bottom: 13px;
    }

    .miniHelp {
      font-size: 12px;
      color: #888;
      line-height: 1.4;
    }

    img {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
    }

    @media (max-width: 560px) {
      .grid2 {
        grid-template-columns: 1fr;
      }

      .columnConfig {
        grid-template-columns: 1fr;
      }

      .columnTitle {
        padding-bottom: 0;
      }
    }
  </style>
</head>

<body>
  <div class="panel">
    <label>
      Select book example hp3e.html (harry potter 3 in English)
      <select id="bookSelect"></select>
    </label>

    <div class="configBox">
      <div class="configTitle">Config export</div>

      <div class="grid2">
        <label>
          cantidad de columnas
          <select id="cantidadColumnas">
            <option value="3" selected>3 columnas</option>
            <option value="4">4 columnas</option>
            <option value="5">5 columnas</option>
            <option value="6">6 columnas</option>
            <option value="7">7 columnas</option>
            <option value="8">8 columnas</option>
            <option value="9">9 columnas</option>
            <option value="10">10 columnas</option>
          </select>
        </label>

        <label>
          gapColumnas
          <input id="gapColumnas" type="number" min="0" step="1" value="7">
        </label>
      </div>

      <div class="miniHelp">
        Cada columna tiene cantidad de líneas y palabras por línea.  
        Ejemplo clásico: columna 1 = 1 línea de 2 palabras, columna 2 = 1 línea de 4 palabras, columna 3 = 3 líneas de 1 palabra.
      </div>

      <div id="columnsBox" class="columnsBox"></div>

      <label class="checkLabel">
        <input id="lineaVaciaEntreBloques" type="checkbox" checked>
        línea vacía entre bloques
      </label>
    </div>

    <button id="exportBtn">Export TXT</button>

    <br>
    You can chose the the better font to more efficient display in your Kindle Device

    <br><br>

    <img src="example.jpg" width="400px" height="500px">

    <div id="status" class="status"></div>
  </div>

  <script>
    <?php
      $libros = array_map('basename', glob('./libros/*.{html,txt}', GLOB_BRACE));
      sort($libros);
    ?>

    const DEFAULT_BOOK_NAMES = <?= json_encode($libros, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const CONFIG = {
      carpetaLibros: "./libros/",

      cantidadColumnas: 3,

      columnas: [
        { lineas: 1, palabrasPorLinea: 2 },
        { lineas: 1, palabrasPorLinea: 4 },
        { lineas: 3, palabrasPorLinea: 1 }
      ],

      gapColumnas: 7,
      lineaVaciaEntreBloques: true,

      incluirEncabezado: false
    };

    const els = {
      bookSelect: document.querySelector("#bookSelect"),
      exportBtn: document.querySelector("#exportBtn"),
      status: document.querySelector("#status"),

      cantidadColumnas: document.querySelector("#cantidadColumnas"),
      columnsBox: document.querySelector("#columnsBox"),
      gapColumnas: document.querySelector("#gapColumnas"),
      lineaVaciaEntreBloques: document.querySelector("#lineaVaciaEntreBloques")
    };

    function setStatus(txt) {
      els.status.textContent = txt || "";
    }

    function getIntFromInput(input, fallback, min = 0) {
      const n = parseInt(input.value, 10);

      if (Number.isNaN(n)) return fallback;

      return Math.max(min, n);
    }

    function clamp(n, min, max) {
      return Math.max(min, Math.min(max, n));
    }

    function defaultColumnConfig(index) {
      const defaults = [
        { lineas: 1, palabrasPorLinea: 2 },
        { lineas: 1, palabrasPorLinea: 4 },
        { lineas: 3, palabrasPorLinea: 1 },
        { lineas: 2, palabrasPorLinea: 1 },
        { lineas: 2, palabrasPorLinea: 1 },
        { lineas: 2, palabrasPorLinea: 1 },
        { lineas: 2, palabrasPorLinea: 1 },
        { lineas: 2, palabrasPorLinea: 1 },
        { lineas: 2, palabrasPorLinea: 1 },
        { lineas: 2, palabrasPorLinea: 1 }
      ];

      return defaults[index] || { lineas: 1, palabrasPorLinea: 1 };
    }

    function syncColumnsLength(amount) {
      amount = clamp(amount, 3, 10);

      while (CONFIG.columnas.length < amount) {
        CONFIG.columnas.push(defaultColumnConfig(CONFIG.columnas.length));
      }

      while (CONFIG.columnas.length > amount) {
        CONFIG.columnas.pop();
      }

      CONFIG.cantidadColumnas = amount;
    }

    function renderColumnControls() {
      const amount = getIntFromInput(els.cantidadColumnas, 3, 3);
      syncColumnsLength(amount);

      els.columnsBox.innerHTML = "";

      CONFIG.columnas.forEach((col, index) => {
        const row = document.createElement("div");
        row.className = "columnConfig";

        row.innerHTML = `
          <div class="columnTitle">Col ${index + 1}</div>

          <label>
            líneas
            <input
              class="colLineas"
              data-index="${index}"
              type="number"
              min="0"
              step="1"
              value="${col.lineas}"
            >
          </label>

          <label>
            palabras por línea
            <input
              class="colPalabras"
              data-index="${index}"
              type="number"
              min="1"
              step="1"
              value="${col.palabrasPorLinea}"
            >
          </label>
        `;

        els.columnsBox.appendChild(row);
      });
    }

    function updateConfigFromInputs() {
      const amount = getIntFromInput(els.cantidadColumnas, 3, 3);
      syncColumnsLength(amount);

      CONFIG.gapColumnas = getIntFromInput(els.gapColumnas, 7, 0);
      CONFIG.lineaVaciaEntreBloques = els.lineaVaciaEntreBloques.checked;

      const lineasInputs = document.querySelectorAll(".colLineas");
      const palabrasInputs = document.querySelectorAll(".colPalabras");

      lineasInputs.forEach(input => {
        const index = parseInt(input.dataset.index, 10);
        if (!CONFIG.columnas[index]) return;

        CONFIG.columnas[index].lineas = getIntFromInput(input, 1, 0);
      });

      palabrasInputs.forEach(input => {
        const index = parseInt(input.dataset.index, 10);
        if (!CONFIG.columnas[index]) return;

        CONFIG.columnas[index].palabrasPorLinea = getIntFromInput(input, 1, 1);
      });
    }

    function toBookPath(name) {
      const raw = String(name || "").trim();

      if (!raw) return CONFIG.carpetaLibros + "hp3.html";

      if (/^(https?:|file:|\/|\.\/|\.\.\/)/i.test(raw)) {
        return raw;
      }

      if (/^libros\//i.test(raw)) {
        return "./" + raw;
      }

      return CONFIG.carpetaLibros + raw;
    }

    function cleanFileName(name) {
      return String(name || "libro")
        .replace(/^\.\/libros\//i, "")
        .replace(/^libros\//i, "")
        .replace(/\.[^.]+$/, "")
        .replace(/[\\/:*?"<>|]/g, "_")
        .trim() || "libro";
    }

    function renderBookSelect() {
      els.bookSelect.innerHTML = "";

      for (const name of DEFAULT_BOOK_NAMES) {
        const opt = document.createElement("option");
        opt.value = toBookPath(name);
        opt.textContent = name;
        els.bookSelect.appendChild(opt);
      }
    }

    function normalizeText(text) {
      return String(text || "")
        .replace(/\u00a0/g, " ")
        .replace(/[ \t]+/g, " ")
        .replace(/\n[ \t]+/g, "\n")
        .replace(/[ \t]+\n/g, "\n")
        .replace(/\n{4,}/g, "\n\n\n")
        .trim();
    }

    function htmlToText(html) {
      const doc = new DOMParser().parseFromString(html, "text/html");

      doc.querySelectorAll("script, style, noscript, svg, canvas").forEach(n => n.remove());

      doc.querySelectorAll("br").forEach(br => {
        br.replaceWith("\n");
      });

      doc.querySelectorAll("p, div, h1, h2, h3, h4, h5, h6, li, section, article").forEach(n => {
        n.insertAdjacentText("beforeend", "\n");
      });

      const raw = doc.body ? doc.body.innerText : doc.documentElement.innerText;

      return normalizeText(raw);
    }

    function txtToText(txt) {
      return normalizeText(txt);
    }

    function splitIntoUnits(text) {
      const chunks = String(text || "").split(/(\n\s*\n|\n)/g);
      const units = [];

      for (const chunk of chunks) {
        if (!chunk) continue;

        if (/^\n\s*\n$/.test(chunk)) {
          units.push({ type: "break" });
          continue;
        }

        if (/^\n$/.test(chunk)) {
          units.push({ type: "softBreak" });
          continue;
        }

        const words = chunk.trim().split(/\s+/).filter(Boolean);

        for (const word of words) {
          units.push({ type: "word", value: word });
        }
      }

      return units;
    }

    function takeWords(units, state, amount) {
      const words = [];

      while (state.i < units.length && words.length < amount) {
        const unit = units[state.i];

        if (unit.type === "break") break;

        if (unit.type === "word") {
          words.push(unit.value);
        }

        state.i++;
      }

      return words;
    }

    function visibleLen(str) {
      return String(str || "").length;
    }

    function spaces(n) {
      return " ".repeat(Math.max(0, Math.floor(n)));
    }

    function buildEscalonadoTxt(units) {
      const lines = [];
      const state = { i: 0 };

      while (state.i < units.length) {
        const unit = units[state.i];

        if (unit.type === "break") {
          lines.push("");
          state.i++;
          continue;
        }

        if (unit.type !== "word") {
          state.i++;
          continue;
        }

        const columnasTexto = [];

        for (let c = 0; c < CONFIG.columnas.length; c++) {
          const colConfig = CONFIG.columnas[c];
          const lineasDeColumna = [];

          for (let l = 0; l < colConfig.lineas; l++) {
            const palabras = takeWords(units, state, colConfig.palabrasPorLinea);

            if (palabras.length) {
              lineasDeColumna.push(palabras.join(" "));
            }

            if (state.i < units.length && units[state.i].type === "break") {
              break;
            }
          }

          columnasTexto.push(lineasDeColumna);

          if (state.i < units.length && units[state.i].type === "break") {
            break;
          }
        }

        const columnasConTexto = columnasTexto.filter(col => col.length > 0);

        if (!columnasConTexto.length) {
          continue;
        }

        const posiciones = [];
        let cursorColumna = 0;

        for (let c = 0; c < columnasConTexto.length; c++) {
          posiciones[c] = cursorColumna;

          const anchoColumna = columnasConTexto[c].reduce((max, linea) => {
            return Math.max(max, visibleLen(linea));
          }, 0);

          cursorColumna += anchoColumna + CONFIG.gapColumnas;
        }

        for (let c = 0; c < columnasConTexto.length; c++) {
          const col = columnasConTexto[c];
          const indent = posiciones[c];

          for (const linea of col) {
            if (linea) {
              lines.push(spaces(indent) + linea);
            }
          }
        }

        if (CONFIG.lineaVaciaEntreBloques) {
          lines.push("");
        }
      }

      return lines
        .join("\n")
        .replace(/[ \t]+\n/g, "\n")
        .replace(/\n{5,}/g, "\n\n\n\n")
        .trimEnd();
    }

    async function fetchBookText(path) {
      const res = await fetch(path, { cache: "no-store" });

      if (!res.ok) {
        throw new Error("No pude cargar el archivo: HTTP " + res.status);
      }

      const raw = await res.text();

      if (/\.txt($|\?)/i.test(path)) {
        return txtToText(raw);
      }

      return htmlToText(raw);
    }

    function downloadTxt(filename, text) {
      const blob = new Blob([text], { type: "text/plain;charset=utf-8" });
      const url = URL.createObjectURL(blob);

      const a = document.createElement("a");
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();

      URL.revokeObjectURL(url);
    }

    function configToText() {
      return CONFIG.columnas.map((col, index) => {
        return "C" + (index + 1) + ":" + col.lineas + "x" + col.palabrasPorLinea;
      }).join(" / ");
    }

    async function exportSelectedBook() {
      updateConfigFromInputs();

      const path = els.bookSelect.value;
      const bookName = cleanFileName(path);

      try {
        els.exportBtn.disabled = true;
        setStatus("Loading...");

        const plainText = await fetchBookText(path);
        const units = splitIntoUnits(plainText);

        if (!units.some(u => u.type === "word")) {
          throw new Error("No encontré texto legible.");
        }

        setStatus("Calculando espacios...");

        let txt = buildEscalonadoTxt(units);

        if (CONFIG.incluirEncabezado) {
          txt =
            "Libro: " + bookName + "\n" +
            "Formato: " + configToText() + "\n\n" +
            txt;
        }

        downloadTxt(bookName + "_scattered.txt", txt);

        setStatus("TXT exported.");
      } catch (err) {
        console.error(err);
        setStatus("Error: " + (err.message || err));
        alert(
          "No pude exportar.\n\n" +
          (err.message || err) +
          "\n\nSi abriste el PHP/HTML con doble click, probá con servidor local:\n\n" +
          "php -S localhost:8000\n\n" +
          "y abrí http://localhost:8000/"
        );
      } finally {
        els.exportBtn.disabled = false;
      }
    }

    els.cantidadColumnas.addEventListener("change", () => {
      updateConfigFromInputs();
      renderColumnControls();
    });

    els.exportBtn.addEventListener("click", exportSelectedBook);

    renderBookSelect();
    renderColumnControls();
    setStatus("Select a book and export.");
  </script>
</body>
</html>