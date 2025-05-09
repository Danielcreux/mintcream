# 📘 Proyecto **MintCream** — Foro Web en PHP + SQLite

## 📌 Resumen General

**MintCream** es una aplicación web tipo foro que permite la gestión de temas, hilos, publicaciones y respuestas, con un sistema jerárquico de usuarios. Está desarrollado con PHP (programación estructurada), utiliza SQLite como base de datos y se ejecuta sobre un servidor Apache (XAMPP).

---

## 🔧 Programación

### 1. Elementos Fundamentales

#### 🔸 Variables y Tipos de Datos

* **Variables de sesión**: `$_SESSION` (gestión de usuarios)
* **Variables superglobales**: `$_POST`, `$_GET`, `$_SERVER`
* **Base de datos**:

  * `INTEGER`: IDs, niveles
  * `TEXT`: nombres, emails, mensajes
  * `DATETIME`: fechas de creación

#### 🔸 Operadores

* Comparación: `==`, `===`, `!=`, `!==`
* Lógicos: `&&`, `||`
* Concatenación: `.`

---

### 2. Estructuras de Control

#### 🔹 Selección

* `if/else` – Control de acceso:

  ```php
  if (isset($_SESSION['user_id']) && $_SESSION['user_level'] === 1) {
      // código para Super Admin
  }
  ```

* `switch` – Mostrar niveles de usuario:

  ```php
  switch ($usuario['nivel']) {
      case 1: echo 'Super Admin'; break;
      case 2: echo 'Admin de Temas'; break;
      case 3: echo 'Admin de Hilos'; break;
      case 4: echo 'Usuario Regular'; break;
  }
  ```

#### 🔹 Repetición

* `for` – Listar temas o hilos:

  ```php
  for ($i = 0; $i < count($temas); $i++) {
      // código para mostrar temas
  }
  ```

* `while` – Iterar resultados desde la base de datos:

  ```php
  while ($tema = $temas->fetchArray()) {
      // mostrar tema
  }
  ```

---

### 3. Control de Excepciones

* Validación de sesiones activas
* Verificación de niveles de usuario
* Validación de datos `POST` y `GET`
* Consultas SQL preparadas para evitar inyecciones

---

### 4. Documentación y Comentarios

* Comentarios por secciones en PHP
* README.md explicativo
* Documentación inline sobre niveles de usuario y tablas

---

### 5. Paradigma Aplicado

* Programación **estructurada** (no POO)
* Código procedural
* Organización por bloques funcionales

---

### 6. Estructura de Archivos

```
/mintcream
├── index.php         # Lógica principal
├── style.css         # Estilos visuales
├── forum.db          # Base de datos SQLite
└── README.md         # Documentación del proyecto
```

---

## 🧩 Base de Datos

### 1. Motor

* **SQLite3**

  * Ligero y embebido
  * No requiere servidor externo
  * Ideal para proyectos medianos y educativos

### 2. Entidades Principales

1. **usuarios**
2. **temas**
3. **hilos**
4. **publicaciones**
5. **respuestas**

### 3. Relaciones

* usuarios (1) → (N) temas
* temas (1) → (N) hilos
* hilos (1) → (N) publicaciones
* publicaciones (1) → (N) respuestas

### 4. Seguridad en BD

* 🔐 `password_hash()` / `password_verify()`
* ✅ `htmlspecialchars()` para salida segura
* 🔒 Consultas SQL preparadas

---

## 🖥️ Interfaz y Tecnologías Web

### 1. Frontend

* **HTML/CSS** simple para formularios y presentación
* No incluye JavaScript ni frameworks

### 2. Interacción DOM

* Basada en envío de formularios
* Sin manipulación con JS

### 3. Validación

* No hay validación formal HTML5
* Sanitización mínima con funciones PHP

---

## ⚙️ Entorno de Desarrollo

* **IDE**: XAMPP
* **Servidor Web**: Apache
* **Lenguaje**: PHP
* **BD**: SQLite3
* **Ruta local**: `c:\xampp\htdocs\mintcream`

---

## 🪛 Mantenimiento y Mejora

### Automatización

* No se implementa automatización de tareas
* Operaciones de mantenimiento (crear tablas, CRUD, sesiones) se realizan manualmente

### Control de Versiones

* No usa Git u otro sistema de versiones
* Organización de archivos básica

### Refactorización Sugerida

✅ Separación por responsabilidades
✅ Validación y consultas seguras
❌ Código monolítico
❌ Falta de clases y capas de abstracción

**Recomendaciones:**

* Modularizar código (separar controladores, vistas)
* Usar POO para usuarios, publicaciones, etc.
* Añadir validaciones del lado cliente (JavaScript)
* Implementar backups automáticos de `forum.db`

---

## 🔐 Jerarquía de Usuario

| Nivel | Rol             |
| ----- | --------------- |
| 1     | Super Admin     |
| 2     | Admin de Temas  |
| 3     | Admin de Hilos  |
| 4     | Usuario Regular |

---

## 🧠 Estructura Lógica General

```plaintext
usuarios
 └── temas
      └── hilos
           └── publicaciones
                └── respuestas
```

---

## 🎯 Objetivo del Proyecto

* Crear una plataforma de foro funcional
* Permitir discusión estructurada
* Controlar jerarquías de permisos
* Gestionar contenido generado por usuarios

---

## 🧱 Stack Tecnológico

* **Backend**: PHP
* **Base de Datos**: SQLite3
* **Frontend**: HTML/CSS (sin JS)
* **Servidor Web**: Apache via XAMPP

---

