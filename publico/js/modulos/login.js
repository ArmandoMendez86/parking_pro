// publico/js/modulos/login.js
// Backend real conectado: app/controladores/LoginControlador.php
// Redirección por rol:
//  - ADMIN  -> /vistas/reportes/index.php
//  - OTROS  -> /vistas/entrada.php

const estado = {
  dirty: false,
  form: { usuario: "", password: "", recordar: false }
};

const ENDPOINT = `${window.URL_BASE || ""}/app/controladores/LoginControlador.php`;

const el = {
  form: document.getElementById("formLogin"),
  usuario: document.getElementById("txtUsuario"),
  password: document.getElementById("txtPassword"),
  recordar: document.getElementById("chkRecordar"),
  btnTogglePass: document.getElementById("btnTogglePass"),
  btnDemo: document.getElementById("btnDemo"),
  btnEntrarSticky: document.getElementById("btnEntrarSticky"),
  btnCancelar: document.getElementById("btnCancelar"),
  sticky: document.getElementById("stickyActions"),
  alerta: document.getElementById("alerta"),
  btnEntrarInline: document.getElementById("btnEntrarInline")
};

function setDirty(v) {
  estado.dirty = !!v;
  el.sticky.classList.toggle("show", estado.dirty);
}

function setAlert(msg, tipo = "danger") {
  if (!msg) {
    el.alerta.classList.add("d-none");
    el.alerta.textContent = "";
    el.alerta.className = "alert d-none";
    return;
  }
  el.alerta.classList.remove("d-none");
  el.alerta.className = `alert alert-${tipo}`;
  el.alerta.textContent = msg;
}

function leerForm() {
  estado.form.usuario = (el.usuario.value || "").trim();
  estado.form.password = (el.password.value || "").trim();
  estado.form.recordar = !!el.recordar.checked;
}

function validar() {
  leerForm();

  const errores = [];
  if (!estado.form.usuario) errores.push("El usuario es obligatorio.");
  if (!estado.form.password) errores.push("La contraseña es obligatoria.");
  if (estado.form.usuario && estado.form.usuario.length < 3) errores.push("El usuario debe tener al menos 3 caracteres.");
  if (estado.form.password && estado.form.password.length < 4) errores.push("La contraseña debe tener al menos 4 caracteres.");

  if (errores.length) {
    setAlert(errores[0], "danger");
    return false;
  }
  setAlert("");
  return true;
}

async function apiPost(action, payload) {
  const url = `${ENDPOINT}?accion=${encodeURIComponent(action)}`;
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload || {})
  });

  const data = await res.json().catch(() => null);
  if (!data || data.ok !== true) {
    const msg = (data && data.mensaje) ? data.mensaje : "Error de comunicación con el servidor";
    throw new Error(msg);
  }
  return data.datos || {};
}

function setLoading(isLoading) {
  const txt = isLoading ? "Entrando..." : "Entrar";
  const html = isLoading
    ? `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${txt}`
    : `<i class="bi bi-box-arrow-in-right me-2"></i>${txt}`;

  el.btnEntrarInline.disabled = isLoading;
  el.btnEntrarSticky.disabled = isLoading;
  el.btnDemo.disabled = isLoading;

  el.btnEntrarInline.innerHTML = html;
  el.btnEntrarSticky.innerHTML = isLoading
    ? `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${txt}`
    : `<i class="bi bi-check2-circle me-2"></i>Entrar`;
}

function redirigirPorRol(rol) {
  const base = window.URL_BASE || "";
  const r = String(rol || "").toUpperCase().trim();

  if (r === "ADMIN") {
    window.location.href = `${base}/vistas/reportes.php`;
    return;
  }

  // CAJERO / OPERADOR / cualquier otro
  window.location.href = `${base}/vistas/entrada.php`;
}

async function loginReal() {
  if (!validar()) return;

  try {
    setLoading(true);
    setAlert("");

    const datos = await apiPost("login", {
      usuario: estado.form.usuario,
      password: estado.form.password,
      recordar: estado.form.recordar
    });

    setDirty(false);
    setAlert(`Bienvenido: ${datos.nombre} (${datos.rol})`, "success");

    // Redirección por rol
    setTimeout(() => redirigirPorRol(datos.rol), 350);

  } catch (e) {
    setAlert(e.message || "Error", "danger");
  } finally {
    setLoading(false);
  }
}

function limpiar() {
  el.usuario.value = "";
  el.password.value = "";
  el.recordar.checked = false;
  setAlert("");
  setDirty(false);
  el.usuario.focus();
}

function togglePassword() {
  const isPass = el.password.type === "password";
  el.password.type = isPass ? "text" : "password";
  el.btnTogglePass.innerHTML = isPass
    ? `<i class="bi bi-eye-slash"></i>`
    : `<i class="bi bi-eye"></i>`;
}

function wireDirty() {
  [el.usuario, el.password, el.recordar].forEach(ctrl => {
    ctrl.addEventListener("input", () => setDirty(true));
    ctrl.addEventListener("change", () => setDirty(true));
  });
}

function wireEventos() {
  el.btnTogglePass.addEventListener("click", togglePassword);

  // demo llena datos y hace login real
  el.btnDemo.addEventListener("click", async () => {
    el.usuario.value = "Admin";
    el.password.value = "1234";
    el.recordar.checked = true;
    setDirty(true);
    setAlert("");
    await loginReal();
  });

  el.form.addEventListener("submit", (e) => {
    e.preventDefault();
    loginReal();
  });

  el.btnEntrarSticky.addEventListener("click", () => loginReal());
  el.btnCancelar.addEventListener("click", () => limpiar());
}

(function init() {
  setAlert("");
  setDirty(false);
  wireDirty();
  wireEventos();
  el.usuario.focus();
})();
