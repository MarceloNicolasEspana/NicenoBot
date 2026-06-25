# Manual del catequista / administrador — Panel de NicenoBot

Este manual es para **catequistas y administradores** que gestionan el contenido,
los participantes y las preguntas de NicenoBot desde el panel privado.

---

## 1. Acceso al panel

1. Ve a **`/login`**.
2. Ingresa tu **correo** y **contraseña**.
3. Llegarás al **Dashboard** (`/admin/nicenito/dashboard`).

> El acceso al panel está protegido por **rol** (`nicenito-admin`). Si al iniciar sesión ves un mensaje
> de "No tienes acceso", pide a un administrador que te asigne el rol (ver sección 8).

**Cuenta de prueba (solo desarrollo):** `admin@nicenito.test` / `password`.

La barra superior tiene cuatro secciones: **Dashboard**, **Contenidos**, **Participantes** y **Preguntas**.

---

## 2. Dashboard

Vista rápida del estado:

- **Contenido semanal activo** (el que ven hoy los jóvenes).
- **Próximo contenido semanal programado**.
- **Contenidos fijos publicados** (total).
- **Borradores pendientes**.
- Accesos directos para **crear contenido semanal** o **fijo**.

---

## 3. Contenidos

NicenoBot responde usando **el contenido que tú preparas**. Hay dos tipos:

| Tipo | Cuándo usarlo | Necesita |
|------|----------------|----------|
| **Semanal** | El tema de la semana (normalmente cambia cada sábado). | Título, resumen, contenido, **referencia del Evangelio** y **fechas** de inicio/término. |
| **Fijo** | Doctrina reutilizable (oración, sacramentos, Trinidad…). | Título, resumen, contenido y **categoría**. No usa fechas. |

### 3.1 Crear / editar contenido
En **Contenidos → Nuevo contenido** (o "Editar"):

- **Tipo:** Semanal o Fijo (los campos cambian según el tipo).
- **Título** y **Slug** (el slug se genera solo desde el título si lo dejas vacío).
- **Resumen:** 1–2 frases; se envía a NicenoBot como contexto.
- **Contenido:** el texto completo del tema.
- **Campos múltiples** (uno por línea, sin escribir JSON):
  - *Referencias bíblicas* (ej. `Juan 1, 1-14`)
  - *Referencias del Catecismo* (ej. `CEC 464-469`)
  - *Ideas clave*
  - *Preguntas de reflexión*
  - *Preguntas frecuentes:* una por línea con el formato **`pregunta :: respuesta`**
  - *Etiquetas* (separadas por coma o por línea) — **muy importantes**: ayudan a que NicenoBot encuentre el contenido correcto.
- **Si es Semanal:** referencia del Evangelio + fecha de **inicio** y **término** (se evalúan en la zona horaria configurada, por defecto `America/Santiago`).
- **Si es Fijo:** elige una **categoría** de la lista.

Botones:
- **Guardar borrador:** lo guarda sin publicar.
- **Publicar:** lo deja visible para NicenoBot (según su estado y, en semanal, sus fechas).

> **Regla de contenido semanal:** no pueden existir **dos semanales publicados con fechas que se solapen**.
> Si intentas publicar uno que choca con otro, el sistema te avisa. Puedes tener varios borradores y semanas futuras programadas.

### 3.2 Acciones en el listado
Filtra por tipo, estado, categoría o título. En cada fila: **Ver**, **Editar**, **Publicar**, **Archivar**, **Duplicar**, **Eliminar**.

- **Archivar:** lo saca de circulación sin borrarlo.
- **Duplicar:** crea una copia en borrador (útil para reutilizar un tema).

### 3.3 Vista previa con NicenoBot
Desde un contenido, abre **"Vista previa con NicenoBot"**:
- Escribe una **pregunta de prueba**.
- Verás el **contenido recuperado**, el **contexto exacto** que se enviaría al modelo, la **respuesta generada** y las **fuentes**.
- **No afecta** conversaciones reales ni guarda la pregunta. Ideal para afinar títulos, etiquetas y FAQ.

---

## 4. Participantes (los jóvenes)

En **Participantes** gestionas a los estudiantes. No usan correo ni contraseña: entran con **código + PIN**.

### 4.1 Crear un participante
1. **Participantes → Crear participante**.
2. Completa:
   - **Nombre completo** (solo visible para el equipo; no aparece en el chat).
   - **Nombre visible** opcional (ej. "Martín P.").
   - **Grupo** (ej. "Confirmación 2026").
   - **Activo** (marcado por defecto).
3. Al guardar, el sistema **genera automáticamente**:
   - un **código** único (ej. `NCE-7F4K`),
   - un **PIN temporal** de 6 dígitos.
4. Se abre una **vista de credenciales imprimible** con el código y el PIN.

### 4.2 Entregar las credenciales
- Usa el botón **Imprimir** o anota el **código + PIN** y entrégaselos al joven.
- ⚠️ **El PIN temporal solo se muestra esa vez.** Si sales de esa pantalla, ya no se vuelve a ver.
  Si se pierde, usa **"Regenerar PIN"** (genera uno nuevo y obliga al joven a crear su PIN otra vez).

### 4.3 Otras acciones
- **Editar:** cambiar nombre, grupo, estado.
- **Activar / Desactivar:** un participante inactivo **no puede entrar**.
- **Regenerar PIN:** nuevo PIN temporal (el código no cambia).
- **Regenerar código:** nuevo código (el PIN no cambia).
- **Eliminar:** baja lógica (se puede recuperar a nivel de base de datos si hiciera falta).
- El número en la columna **Preguntas** enlaza al historial de ese participante.

### 4.4 Qué pasa en el primer ingreso del joven
Con su código + PIN temporal, en su primer acceso el joven **crea su PIN personal** y **acepta el aviso de privacidad**. A partir de ahí entra con su PIN propio.

---

## 5. Preguntas

En **Preguntas** ves lo que consultan los jóvenes, para entender los temas del grupo y dar acompañamiento.

### 5.1 Filtros
- Participante, grupo, fecha, categoría, contenido semanal, estado de seguimiento.
- Casillas útiles: **Requiere acompañamiento**, **Sin contenido suficiente**, **Sin respuesta**.

### 5.2 Detalle de una pregunta
Abre **"Ver"**:
- Pregunta y respuesta de NicenoBot.
- **Fuentes** utilizadas.
- Datos del participante, categoría, si usó IA, si requería acompañamiento, fecha.

### 5.3 Marcar seguimiento
En el detalle puedes cambiar el **estado de seguimiento** y agregar **notas privadas**:

| Estado | Significado sugerido |
|--------|----------------------|
| **Sin marcar** | Aún no revisada. |
| **Para revisar** | Necesita una mirada. |
| **Seguimiento catequista** | Conviene conversarlo con el joven. |
| **Resuelto** | Ya se acompañó / cerrado. |

Las **notas** son privadas del equipo. (No se crean rankings ni evaluaciones de "fe" o conducta.)

---

## 6. Privacidad y retención de datos

- En los **registros técnicos (logs)** solo se guardan métricas (longitud, categoría, uso de IA, tiempos, errores). **Nunca** se registran PIN, códigos, ni el texto completo de preguntas/respuestas.
- El **texto** de preguntas y respuestas vive en la tabla de preguntas y se **anonimiza automáticamente** pasado el período de retención (por defecto **90 días**, configurable con `NICENITO_QUESTION_RETENTION_DAYS`).
- La anonimización se ejecuta con el comando:
  ```bash
  php artisan nicenito:prune-questions
  ```
  Borra el texto de pregunta, respuesta, fuentes y notas de las más antiguas, **conservando solo estadísticas agregadas** (categoría, uso de IA, conteos, fechas). Se puede programar para que corra solo (consultar al equipo técnico).

---

## 7. Flujo recomendado de cada semana

1. **Antes del sábado:** crea el **contenido semanal** (Evangelio + resumen + ideas clave + FAQ + etiquetas) y prográmalo con sus fechas.
2. Revisa que tus **contenidos fijos** clave estén publicados (Trinidad, oración, sacramentos…).
3. Usa la **Vista previa** para comprobar que NicenoBot responde bien a preguntas típicas.
4. Entrega **códigos y PIN** a los jóvenes nuevos.
5. Durante la semana, revisa **Preguntas** y marca las que necesiten **seguimiento**.

---

## 8. Notas técnicas (administrador)

- **Asignar el rol de acceso** a un usuario (requiere que el usuario exista):
  ```bash
  php artisan tinker
  >>> App\Models\User::where('email','correo@dominio.cl')->first()->assignRole('nicenito-admin');
  ```
  Si el rol no existe aún: `Spatie\Permission\Models\Role::findOrCreate('nicenito-admin');`
- **Datos de prueba:** `php artisan db:seed` crea el admin demo, contenidos de ejemplo y el participante demo (`NCE-DEMO` / `123456`). **No usar en producción.**
- **Zona horaria** del contenido semanal: `config/nicenito.php → timezone` (por defecto `America/Santiago`).
- **Límites del joven:** 1 pregunta cada 8 s, 5 preguntas cada 15 min, 5 intentos de acceso cada 15 min (configurables en `config/nicenito.php → participant`).
