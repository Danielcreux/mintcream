<?php
session_start();
$db = new SQLite3('forum.db');

// Crear tablas si no existen
$db->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        nivel INTEGER NOT NULL DEFAULT 4 -- 1: Super Admin, 2: Tema Admin, 3: Hilo Admin, 4: Usuario
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS temas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        creado_por INTEGER NOT NULL,
        FOREIGN KEY (creado_por) REFERENCES usuarios(id)
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS hilos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        tema_id INTEGER NOT NULL,
        creado_por INTEGER NOT NULL,
        FOREIGN KEY (tema_id) REFERENCES temas(id),
        FOREIGN KEY (creado_por) REFERENCES usuarios(id)
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS publicaciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        contenido TEXT NOT NULL,
        hilo_id INTEGER NOT NULL,
        creado_por INTEGER NOT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hilo_id) REFERENCES hilos(id),
        FOREIGN KEY (creado_por) REFERENCES usuarios(id)
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS respuestas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        contenido TEXT NOT NULL,
        publicacion_id INTEGER NOT NULL,
        creado_por INTEGER NOT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (publicacion_id) REFERENCES publicaciones(id),
        FOREIGN KEY (creado_por) REFERENCES usuarios(id)
    )
");

// Crear super admin si no existe
$superAdmin = $db->querySingle("SELECT id FROM usuarios WHERE nivel = 1", true);
if (!$superAdmin) {
    $db->exec("
        INSERT INTO usuarios (usuario, email, password, nivel)
        VALUES ('danielcreux', 'jd2000_fs@hotmail.com', '" . password_hash('danielcreux', PASSWORD_DEFAULT) . "', 1)
    ");
}

// Manejar el inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Buscar al usuario por su nombre de usuario
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->bindValue(':usuario', $usuario, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Iniciar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_level'] = $user['nivel'];
        header('Location: index.php');
        exit();
    } else {
        echo "<p style='color: red;'>Credenciales incorrectas.</p>";
    }
}

// Manejar el cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Manejar la creación de temas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_tema'])) {
    if (isset($_SESSION['user_id']) && ($_SESSION['user_level'] === 1 || $_SESSION['user_level'] === 2)) {
        $nombre = $_POST['nombre_tema'];
        $creado_por = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO temas (nombre, creado_por) VALUES (:nombre, :creado_por)");
        $stmt->bindValue(':nombre', $nombre, SQLITE3_TEXT);
        $stmt->bindValue(':creado_por', $creado_por, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Manejar la creación de hilos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_hilo'])) {
    if (isset($_SESSION['user_id']) && ($_SESSION['user_level'] === 1 || $_SESSION['user_level'] === 2 || $_SESSION['user_level'] === 3)) {
        $titulo = $_POST['titulo_hilo'];
        $tema_id = $_POST['tema_id'];
        $creado_por = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO hilos (titulo, tema_id, creado_por) VALUES (:titulo, :tema_id, :creado_por)");
        $stmt->bindValue(':titulo', $titulo, SQLITE3_TEXT);
        $stmt->bindValue(':tema_id', $tema_id, SQLITE3_INTEGER);
        $stmt->bindValue(':creado_por', $creado_por, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Manejar la creación de publicaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_publicacion'])) {
    if (isset($_SESSION['user_id'])) {
        $contenido = $_POST['contenido'];
        $hilo_id = $_POST['hilo_id'];
        $creado_por = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO publicaciones (contenido, hilo_id, creado_por) VALUES (:contenido, :hilo_id, :creado_por)");
        $stmt->bindValue(':contenido', $contenido, SQLITE3_TEXT);
        $stmt->bindValue(':hilo_id', $hilo_id, SQLITE3_INTEGER);
        $stmt->bindValue(':creado_por', $creado_por, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Manejar la creación de respuestas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_respuesta'])) {
    if (isset($_SESSION['user_id'])) {
        $contenido = $_POST['contenido_respuesta'];
        $publicacion_id = $_POST['publicacion_id'];
        $creado_por = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO respuestas (contenido, publicacion_id, creado_por) VALUES (:contenido, :publicacion_id, :creado_por)");
        $stmt->bindValue(':contenido', $contenido, SQLITE3_TEXT);
        $stmt->bindValue(':publicacion_id', $publicacion_id, SQLITE3_INTEGER);
        $stmt->bindValue(':creado_por', $creado_por, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Manejar la creación de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    if (isset($_SESSION['user_id']) && $_SESSION['user_level'] === 1) {
        $usuario = $_POST['usuario'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nivel = $_POST['nivel'];
        $stmt = $db->prepare("INSERT INTO usuarios (usuario, email, password, nivel) VALUES (:usuario, :email, :password, :nivel)");
        $stmt->bindValue(':usuario', $usuario, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $stmt->bindValue(':nivel', $nivel, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Manejar la eliminación de usuarios
if (isset($_GET['eliminar_usuario'])) {
    if (isset($_SESSION['user_id']) && $_SESSION['user_level'] === 1) {
        $id = $_GET['eliminar_usuario'];
        $db->exec("DELETE FROM usuarios WHERE id = $id");
        header('Location: index.php');
        exit();
    }
}

// Manejar la actualización de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    if (isset($_SESSION['user_id']) && $_SESSION['user_level'] === 1) {
        $id = $_POST['id'];
        $usuario = $_POST['usuario'];
        $email = $_POST['email'];
        $nivel = $_POST['nivel'];
        $stmt = $db->prepare("UPDATE usuarios SET usuario = :usuario, email = :email, nivel = :nivel WHERE id = :id");
        $stmt->bindValue(':usuario', $usuario, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':nivel', $nivel, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Obtener datos para mostrar
$temas = $db->query("SELECT * FROM temas");
$hilos = $db->query("SELECT * FROM hilos");
$publicaciones = $db->query("
    SELECT publicaciones.*, usuarios.usuario 
    FROM publicaciones 
    JOIN usuarios ON publicaciones.creado_por = usuarios.id
");
$usuarios = $db->query("SELECT * FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Foro Online</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Bienvenido, <?= htmlspecialchars($db->querySingle("SELECT usuario FROM usuarios WHERE id = " . $_SESSION['user_id'])) ?> 
            <a href="?logout" class="btn">Cerrar Sesión</a></p>
            <?php if ($_SESSION['user_level'] === 1): ?>
                <a href="?page=admin" class="btn">Panel de Administración</a>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST" class="login-form">
                <input type="text" name="usuario" placeholder="Nombre de Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Iniciar Sesión</button>
            </form>
        <?php endif; ?>
    </header>

    <main>
        <?php
        // Enrutamiento básico
        $page = isset($_GET['page']) ? $_GET['page'] : 'foro';

        if ($page === 'admin' && isset($_SESSION['user_id']) && $_SESSION['user_level'] === 1) {
            // Panel de Administración
            ?>
            <div class="section">
                <h2>Gestión de Usuarios</h2>
                <!-- Formulario para agregar un nuevo usuario -->
                <form method="POST">
                    <h3>Agregar Nuevo Usuario</h3>
                    <input type="text" name="usuario" placeholder="Nombre de Usuario" required>
                    <input type="email" name="email" placeholder="Correo Electrónico" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <select name="nivel" required>
                        <option value="1">Super Admin</option>
                        <option value="2">Admin de Temas</option>
                        <option value="3">Admin de Hilos</option>
                        <option value="4">Usuario</option>
                    </select>
                    <button type="submit" name="crear_usuario">Crear Usuario</button>
                </form>

                <!-- Lista de usuarios existentes -->
                <h3>Usuarios Registrados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Nivel</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($usuario = $usuarios->fetchArray()): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['id']) ?></td>
                                <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td>
                                    <?php
                                    switch ($usuario['nivel']) {
                                        case 1: echo 'Super Admin'; break;
                                        case 2: echo 'Admin de Temas'; break;
                                        case 3: echo 'Admin de Hilos'; break;
                                        case 4: echo 'Usuario'; break;
                                        default: echo 'Desconocido';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="?eliminar_usuario=<?= $usuario['id'] ?>">Eliminar</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                        <input type="text" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
                                        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                        <select name="nivel" required>
                                            <option value="1" <?= $usuario['nivel'] == 1 ? 'selected' : '' ?>>Super Admin</option>
                                            <option value="2" <?= $usuario['nivel'] == 2 ? 'selected' : '' ?>>Admin de Temas</option>
                                            <option value="3" <?= $usuario['nivel'] == 3 ? 'selected' : '' ?>>Admin de Hilos</option>
                                            <option value="4" <?= $usuario['nivel'] == 4 ? 'selected' : '' ?>>Usuario</option>
                                        </select>
                                        <button type="submit" name="actualizar_usuario">Actualizar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php
        } else {
            // Foro Online
            ?>
            <div class="section">
                <h2>Temas</h2>
                <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_level'] === 1 || $_SESSION['user_level'] === 2)): ?>
                    <form method="POST">
                        <input type="text" name="nombre_tema" placeholder="Nombre del Tema" required>
                        <button type="submit" name="agregar_tema">Agregar Tema</button>
                    </form>
                <?php endif; ?>
                <ul>
                    <?php while ($tema = $temas->fetchArray()): ?>
                        <li>
                            <strong><?= htmlspecialchars($tema['nombre']) ?></strong>
                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_level'] === 1 || $_SESSION['user_level'] === 2)): ?>
                                <a href="?eliminar_tema=<?= $tema['id'] ?>">Eliminar</a>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="section">
                <h2>Hilos</h2>
                <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_level'] === 1 || $_SESSION['user_level'] === 2 || $_SESSION['user_level'] === 3)): ?>
                    <form method="POST">
                        <input type="text" name="titulo_hilo" placeholder="Título del Hilo" required>
                        <select name="tema_id" required>
                            <?php
                            $temas = $db->query("SELECT * FROM temas");
                            while ($tema = $temas->fetchArray()): ?>
                                <option value="<?= $tema['id'] ?>"><?= htmlspecialchars($tema['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" name="agregar_hilo">Agregar Hilo</button>
                    </form>
                <?php endif; ?>
                <ul>
                    <?php while ($hilo = $hilos->fetchArray()): ?>
                        <li>
                            <strong><?= htmlspecialchars($hilo['titulo']) ?></strong>
                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_level'] === 1 || $_SESSION['user_level'] === 2 || $_SESSION['user_level'] === 3)): ?>
                                <a href="?eliminar_hilo=<?= $hilo['id'] ?>">Eliminar</a>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="section">
                <h2>Publicaciones</h2>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST">
                        <textarea name="contenido" placeholder="Escribe tu publicación" required></textarea>
                        <select name="hilo_id" required>
                            <?php
                            $hilos = $db->query("SELECT * FROM hilos");
                            while ($hilo = $hilos->fetchArray()): ?>
                                <option value="<?= $hilo['id'] ?>"><?= htmlspecialchars($hilo['titulo']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" name="agregar_publicacion">Publicar</button>
                    </form>
                <?php endif; ?>
                <ul>
                    <?php while ($publicacion = $publicaciones->fetchArray()): ?>
                        <li>
                            <strong><?= htmlspecialchars($publicacion['contenido']) ?></strong>
                            <small>Publicado por: <?= htmlspecialchars($publicacion['usuario']) ?> el <?= $publicacion['fecha_creacion'] ?></small>
                            <!-- Formulario para agregar respuestas -->
                            <form method="POST" style="margin-top: 10px;">
                                <textarea name="contenido_respuesta" placeholder="Escribe una respuesta" required></textarea>
                                <input type="hidden" name="publicacion_id" value="<?= $publicacion['id'] ?>">
                                <button type="submit" name="agregar_respuesta">Responder</button>
                            </form>
                            <!-- Mostrar respuestas -->
                            <?php
                            $respuestas = $db->query("
                                SELECT respuestas.*, usuarios.usuario 
                                FROM respuestas 
                                JOIN usuarios ON respuestas.creado_por = usuarios.id
                                WHERE publicacion_id = " . $publicacion['id']
                            );
                            while ($respuesta = $respuestas->fetchArray()): ?>
                                <div style="margin-left: 20px; margin-top: 10px;">
                                    <strong><?= htmlspecialchars($respuesta['usuario']) ?>:</strong>
                                    <?= htmlspecialchars($respuesta['contenido']) ?>
                                    <small>(<?= $respuesta['fecha_creacion'] ?>)</small>
                                </div>
                            <?php endwhile; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <?php
        }
        ?>
    </main>
</body>
</html>