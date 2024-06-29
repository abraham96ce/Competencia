<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 
    include 'cabecera.php';
    require_once "/srv/www/competencia/registro/config/conexion_db.php";

    require '/srv/www/competencia/admin/PHPMailer/Exception.php';
    require '/srv/www/competencia/admin/PHPMailer/PHPMailer.php';
    require '/srv/www/competencia/admin/PHPMailer/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    /**
     * Función que se utiliza para enviar correos.
     * @param array $destCorreo Correo del o los destinatarios.
     * @param string $mensaje Mensaje del correo.
     * @param string $destNombre Nombre del destinatario.
     * @param string|null $asunto Asunto del correo.
     * @return void
     */
    function EnviarCorreo(array $destCorreo, string $mensaje, string $destNombre = null, string $asunto = null) : bool {

        include_once "/srv/www/competencia/admin/config_correo.php";

        // Enviar 'true' al constructor habilita las excepciones
        $mail = new PHPMailer(true);

        try {
            // Se establece la configuración del servidor
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;              //Habilita el reporte detallado de la operación

            // Configuración para enviar a través de SMTP.
            $mail->isSMTP();

            // Servidor SMTP.
            $mail->Host = $host;

            // Habilita la autenticación en SMTP (NECESARIO).
            $mail->SMTPAuth = true;

            // Nombre del usuario para SMTP (Correo electrónico del remitente).
            $mail->Username = $username;

            // Contraseña para acceder al correo electrónico remitente.
            $mail->Password = $password;

            $mail->SMTPSecure = $smtpSecure;

            // Puerto TCP para la conexión.
            $mail->Port = $port;

            // Para poder escribir con tildes.
            $mail->CharSet = "UTF-8";

            // Remitente y destinatario
            $mail->setFrom($username, $fromName);

            $mail->Helo = $host;

            $mail->SMTPOptions = array(
                "ssl" => array(
                    "verify_peer"       => false,
                    "verify_peer_name"  => false,
                    "allow_self_signed" => true
                )
            );

            // Destinatarios (el nombre del destinatario es opcional).
            foreach ($destCorreo as $destinatario) {
                $mail->addAddress($destinatario, $destNombre);
            }

            // Copias visibles para el destinatario.
            // $mail->addCC('cc@example.com');

            // Copias ocultas.
            $mail->addBCC($username);

            // Adjuntar archivo
            // $mail->addAttachment('/var/tmp/file.tar.gz');

            // Se puede cambiar el nombre con el que se recibe el archivo.
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');

            // El correo se envía en formato HTML.
            $mail->isHTML(true);

            // Asunto del correo.
            $mail->Subject = $asunto;

            // Cuerpo del correo o mensaje.
            $mail->Body = $mensaje;

            $mail->send();

            return true;

            } catch (Exception $e) {
                error_log('enviar-config_correo.php::EnviarCorreo -> Error al enviar el correo.');
                return false;
        }
    }

?>

<body style="background-color: #b9b9bd">

    <?php
        if (!session_start()) {
                header("Location: https://competencia.mat.uson.mx/registro");
            }

        // Inicializa la variable de sesión para el aviso
        if (!isset($_SESSION['datos_guardados'])) {
            $_SESSION['datos_guardados'] = false;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Obtiene los datos del formulario
            $iduser = isset($_POST["iduser"]) ? test_input($_POST["iduser"]) : "";
            $nombre = isset($_POST["nombre"]) ? test_input($_POST["nombre"]) : "";
            $contrasena = isset($_POST["contrasena"]) ? test_input($_POST["contrasena"]) : "";
            $contrasena = sha1($contrasena);
            $correo = isset($_POST["correo"]) ? test_input($_POST["correo"]) : "";
            $edicion = isset($_POST["edicion"]) ? test_input($_POST["edicion"]) : "";
        
            $error_messages = array(); // Inicializar el array de mensajes de error
        
            // Verifica si el correo electrónico ya existe en la base de datos para esta edición
            $query_verificar_correo = "SELECT * FROM comite WHERE comite_correo = '$correo' AND comite_edicion = '$edicion'";
            $result_verificar_correo = mysqli_query($db_connection, $query_verificar_correo);

            // Verifica si el correo electrónico ya existe en la base de datos para esta edición en la tabla usuario
            $query_verificar_correo_usuario = "SELECT * FROM usuario WHERE usuario_nombre = '$correo' AND usuario_edicion = '$edicion'";
            $result_verificar_correo_usuario = mysqli_query($db_connection, $query_verificar_correo_usuario);
        
            if (mysqli_num_rows($result_verificar_correo) > 0) {
                $error_messages[] = "¡El correo electrónico ya está registrado en esta edición!";
            }
        
            // Verifica si el nombre ya está registrado en esta edición
            /*$query_verificar_nombre = "SELECT * FROM comite WHERE comite_nombre = '$nombre' AND comite_edicion = '$edicion'";
            $result_verificar_nombre = mysqli_query($db_connection, $query_verificar_nombre);
        
            if (mysqli_num_rows($result_verificar_nombre) > 0) {
                $error_messages[] = "¡El nombre ya está registrado en esta edición. Por favor, utilice otro nombre!";
            }
        
            // Verifica si el ID ya está en uso en esta edición
            $comite_id = $edicion . '2' . $iduser;
            $query_verificar_id = "SELECT * FROM comite WHERE comite_id = '$comite_id'";
            $result_verificar_id = mysqli_query($db_connection, $query_verificar_id);
        
            if (mysqli_num_rows($result_verificar_id) > 0) {
                $error_messages[] = "¡El ID ya está en uso en esta edición!";
            }

            // Se reemplaza los errores específicos por el mensaje general para no usar ambos mensajes.
            if (!empty($error_messages)) {
                if (in_array("¡El correo electrónico ya está registrado en esta edición!", $error_messages) &&
                    in_array("¡El ID ya está en uso en esta edición!", $error_messages)) {
                    $error_messages = array("Usuario ya registrado en la edición");
                }
            }*/
        
             // Si no hay errores, procede con la inserción
            if (empty($error_messages)) {
                $query_insertar = "INSERT INTO comite (comite_nombre, comite_clave, comite_rol, comite_correo, comite_fechaRegistro, comite_activo, comite_edicion) VALUES ('$nombre', '$contrasena', 2, '$correo', now(), 1, '$edicion')";
                if (mysqli_query($db_connection, $query_insertar)) {
                    // Inserta los datos también en la tabla de usuarios (Usuario)
                    $query_insertar_usuario = "INSERT INTO usuario (usuario_nombre, usuario_clave, usuario_rol, usuario_edicion, usuario_fecha_reg, usuario_activo) VALUES ('$correo', '$contrasena', 2, $edicion, now(), 1)";
                    mysqli_query($db_connection, $query_insertar_usuario);

                    $_SESSION['datos_guardados'] = true; // Establece la variable de sesión a verdadero si los datos se guardaron correctamente

                    // Genera una clave de acceso aleatoria
                    //$clave_acceso = bin2hex(random_bytes(8)); // Genera una clave de 8 bytes (16 caracteres hexadecimales) 

                    // Almacena la clave de acceso en la base de datos
                    //$query_actualizar_clave = "UPDATE comite SET comite_clave = '{$clave_acceso}' WHERE comite_id = '{$comite_id}'";
                    //mysqli_query($db_connection, $query_actualizar_clave);

                    // Envia el correo electrónico de confirmación
                    $asunto = "Confirmación de registro";

                    // Genera un token único para el enlace usando el correo electrónico del usuario
                    //$token = base64_encode($correo);
                    $token = bin2hex(random_bytes(20));

                    //2024. Se obtiene el usuario recien ingresado (con MAX) para insertar el token de activacion
                    //y poder agregarlo al correo que se envia.
                    //obtener el id del recien-usuario insertado.
                    $query_maximoId = "SELECT MAX(usuario_id) FROM usuario";
                    $usuario_id01 = mysqli_query($db_connection, $query_maximoId);
                    //$usuario_idDB = mysqli_fetch_assoc($usuario_id01);
                    $usuario_idDB = mysqli_fetch_row($usuario_id01);
                    //guardar enlace de activacion.
                    //echo "::".$usuario_id01."::";
                    //echo "::".$usuario_idDB."::";
                    //echo "::".$usuario_idDB[0]."::";
                    $query_meterEnlace= "INSERT INTO enlace_activacion (usuario_id, token) VALUES ('$usuario_idDB[0]','$token')";
                    mysqli_query($db_connection, $query_meterEnlace);

                    // Crea el enlace
                    $enlace = "https://competencia.mat.uson.mx/registro/usuario-activar.php?tkn=" . urlencode($token);
                    //Creando el mensaje.
                    $mensaje = "<p>Hola {$nombre},</p>";
                    $mensaje .= "<p>Has sido registrado como validador {$usuario_id} en nuestro sistema. A continuación, encontrarás la información necesaria para acceder a tu cuenta:</p>";
                    $mensaje .= "<p><strong>Correo:</strong> {$correo}</p>";
                    $mensaje .= "<p>Para continuar con tu registro, es necesario que proporcione información en el siguiente enlace:</p>";
                    $mensaje .= "<p><a href='$enlace' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>Crear Contraseña</a></p>";
                    $mensaje .= "<p>Por favor, utiliza esta información para iniciar sesión en nuestro sistema. Si tienes alguna pregunta o necesitas asistencia, no dudes en ponerte en contacto con el administrador.</p>";
                    $mensaje .= "<p>Atentamente,<br>Comité Organizador de la Competencia de Matemáticas por Equipos<br>Departamento de Matemáticas de la Universidad de Sonora.</p>";
                    
                    // Envia el correo electrónico
                    if (EnviarCorreo([$correo], $mensaje, $nombre, $asunto)) {
                        echo "<script>alert('Se ha enviado un correo electrónico de confirmación al usuario.');</script>";
                    } else {
                        echo "<script>alert('Error al enviar el correo electrónico de confirmación.');</script>";
                    }
                } else {
                    $error_messages[] = "Error al insertar en la base de datos.";
                }
            }
        }
    
        
        function test_input($data) {
            // Impedir inyección de código o cosas raras de 'hackeo'.
            // https://www.w3schools.com/php/php_form_validation.asp
            // Verificar si $data no es nulo antes de aplicar la función trim()
            if ($data !== null) {
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
            }
            return $data;
        }
        ?>

    <div class="cabecera">
        <?php include 'titulo.php';?>
    </div><br><br>

    <div style="max-width: 700px; margin: 0 auto;">
        <div style="border: 1px solid #dddddd; padding: 20px; background-color: #f5f5f5; border-radius: 20px; box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75);">
            <?php if ($_SESSION['datos_guardados']) : ?>
                <h6 style="text-align: center; font-size: 14px; color: red;">
                    [Usuario <?php echo $correo; ?> ¡Almacenado!]
                </h6>
            <?php endif; ?>
            <?php if (!empty($error_messages)) : ?>
                <?php foreach ($error_messages as $error) : ?>
                    <p style="color: red; text-align: center;"><?php echo $error; ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
            <h1 style="text-align: center">Formulario de Validador</h1>
            
            <p style="text-align: center">Completa todos los campos requeridos para proceder con el registro.</p>

            <h2 style="margin-left: 30px; margin-bottom: -10px;">Información del validador</h2> 

            <form id="estiloEduardo.css" class="form-control-1" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline-block; text-align: left"> 
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="nombre" style="margin-right: 10px; margin-left: 25px;">Nombre<span style="color: red">*</span></label><br>
                    <input class="form-control" type="text" id="nombre" name="nombre" style="margin-left: 25px" required>
                </div>
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="correo" style="margin-right: 10px; margin-left: 25px">Correo<span style="color: red">*</span></label><br>
                    <input class="form-control" type="email" id="correo" name="correo" style="margin-left: 25px" required>
                
                </div>
                <!--<div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="iduser" style="margin-right: 10px; margin-left: 25px">ID</label><br>
                    <input class="form-control" type="text" id="iduser" name="iduser" value="0" style="margin-left: 25px">
                </div>-->
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="edicion" style="margin-right: 10px; margin-left: 25px">Edición</label><br>
                    <input class="form-control" type="text" id="edicion" name="edicion" min="0" pattern="\d+" style="margin-left: 25px" title="Por favor, ingrese solo números enteros positivos" value="<?php echo date('Y'); ?>">
                </div>
                <div style="text-align: center;">
                    <input type="submit" value="Alta validador" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" style="margin-right: 30px;">
                    <div style="display: inline-block;">
                        <a href="menuAdmin.php" style="text-decoration: none;"><input type="button" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" value="Regreso"></a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <br><br>

    <div class="footer">
        <?php include 'piePagina.php';?>
    </div>
    
</body>
</html>
