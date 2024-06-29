<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 
    include 'cabecera.php';
    require_once "/srv/www/competencia/registro/config/conexion_db.php";
    //require_once "../registro/config/conexion_db.php"; //por estamos trabajando en /srv/www/competencia/admin.
    //opcional. copiar a una carpeta local.
?>

<body style="background-color: #b9b9bd">

    <?php
		if(!session_start()){
            header("Location: https://competencia.mat.uson.mx/registro");
        }
	?>

    <div class="cabecera">
        <?php include 'titulo.php';?>
    </div><br><br>

    <?php
     // Inicializa la variable de sesión para el aviso
    if (!isset($_SESSION['datos_examen_guardados'])) {
        $_SESSION['datos_examen_guardados'] = false;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $titulo = isset($_POST["titulo"]) ? test_input($_POST["titulo"]) : "";
        $edicion = isset($_POST["edición"]) ? test_input($_POST["edición"]) : "";
        $fecha_activacion = isset($_POST["fecha_activacion"]) ? test_input($_POST["fecha_activacion"]) : "";
        $fecha_final = isset($_POST["fecha_final"]) ? test_input($_POST["fecha_final"]) : "";
        $num_examen = isset($_POST["num_examen"]) ? test_input($_POST["num_examen"]) : "";
        $archivo_pdf = $_FILES["archivo_pdf"]["name"];
        $archivo_tmp = $_FILES["archivo_pdf"]["tmp_name"];

        // Verifica que $edicion sea un año válido (entre 1000 y 9999)
        if (!is_numeric($edicion) || strlen($edicion) !== 4 || $edicion < 1000 || $edicion > 9999) {
            echo "¡Error! El año de edición proporcionado no es válido.";
            exit;
        }

        // Verifica si ya existe un examen con el mismo título y edición
        $query_verificar = "SELECT * FROM examen WHERE examen_titulo = '$titulo' AND examen_edicion = '$edicion'";
        $resultado = mysqli_query($db_connection, $query_verificar);

        //$ruta_destino = "/srv/www/competencia/registro/examenes/$edicion/";
        $ruta_destino = "../registro/examenes/$edicion/";
            if (is_dir($ruta_destino)) {
                //$mensaje_error = "La carpeta ya existe: $ruta_destino";
                //echo $mensaje_error;
            } else {
                if ( mkdir($ruta_destino, 0777, false) ) {
                    //$mensaje_error = "Carpeta creada exitosamente: $ruta_destino";
                    //echo $mensaje_error;
                } else {
                    //$mensaje_error = "¡Error al crear la carpeta de destino! Verifica los permisos.";
                    //echo $mensaje_error;
                }
            }
            
        move_uploaded_file($archivo_tmp, $ruta_destino . $archivo_pdf);

        if (mysqli_num_rows($resultado) > 0) {
           $mensaje_error = "¡Error! ¡El examen ya está registrado!";
        } else {
            // Insertar el examen en la base de datos
            $query_insertarExamen = "INSERT INTO examen (examen_titulo, examen_edicion, examen_apertura, examen_cierre, examen_archivo, examen_n_reactivos, examen_n_puntos) VALUES ('$titulo', '$edicion', '$fecha_activacion', '$fecha_final', '$archivo_pdf', $num_examen, 0)";
                        
            // Ejecuta la consulta para insertar el examen
            if (mysqli_query($db_connection, $query_insertarExamen)) {
                // Obtiene el ID del examen recién insertado
                $id_examen = mysqli_insert_id($db_connection);

                // Itera sobre los reactivos y dar de alta cada uno de ellos en la tabla "reactivo_exam"
                for ($i = 1; $i <= $num_examen; $i++) {
                    $query_insertarReactivo = "INSERT INTO reactivo_exam (examen_id, reactivo_n, puntos_posibles) VALUES ($id_examen, $i, 0)";
                    if (mysqli_query($db_connection, $query_insertarReactivo)) {
                        // Éxito al insertar el reactivo
                    } else {
                        echo "¡Error al registrar el reactivo $i del examen $id_examen!: " . mysqli_error($db_connection);
                    }
                }

                $_SESSION['datos_examen_guardados'] = true; // Establece la variable de sesión a verdadero si los datos se guardaron correctamente
            } else {
                echo "¡Error al registrar el examen!: " . mysqli_error($db_connection);
            }

        }
    
        mysqli_close($db_connection);
    }

    function test_input($data) {
        // Impedir inyección de código o cosas raras de 'hackeo'.
        if ($data !== null) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
        }
        return $data;
    }
    ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var fechaActivacion = document.getElementById("fecha_activacion");
        var fechaFinal = document.getElementById("fecha_final");
        var submitButton = document.querySelector("input[type='submit']");

        fechaActivacion.addEventListener("change", validarFechas);
        fechaFinal.addEventListener("blur", validarFechasManualmente);

        function validarFechas() {
            var activacion = new Date(fechaActivacion.value);
            var final = new Date(fechaFinal.value);

            if (final < activacion) {
                alert("La fecha de cierre no puede ser anterior a la fecha de activación.");
                fechaFinal.value = fechaActivacion.value; // Restablece la fecha de cierre a la fecha de activación
            }
            submitButton.disabled = final < activacion; // Deshabilita el botón de envío del formulario si hay un error de validación
        }

        function validarFechasManualmente() {
            if (fechaFinal.value !== '') {
                var activacion = new Date(fechaActivacion.value);
                var final = new Date(fechaFinal.value);

                if (final < activacion) {
                    alert("La fecha de cierre no puede ser anterior a la fecha de activación.");
                    fechaFinal.value = fechaActivacion.value; // Restablece la fecha de cierre a la fecha de activación
                }
                submitButton.disabled = final < activacion; // Deshabilita el botón de envío del formulario si hay un error de validación
            }
        }
    });
</script>


    <div class="container" style="max-width: 700px; margin: 0 auto;">
        <div  style="border: 1px solid #dddddd; padding: 20px; background-color: #f5f5f5; border-radius: 20px; box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75);">
            <?php if ($_SESSION['datos_examen_guardados']) : ?>
                <h6 style="text-align: center; font-size: 14px; color: red;">¡Examen registrado correctamente!</h6>
            <?php endif; ?>

            <?php if(isset($mensaje_error)): ?>
                <div style="text-align: center; color: red;"><?php echo $mensaje_error; ?></div>
            <?php endif; ?>
            <h1 style="text-align: center">Formulario para registrar el examen</h1>
            <p style="text-align: center">Completa todos los campos requeridos para proceder con la activación del examen.</p>
            <h2 style="margin-left: 30px; margin-bottom: -10px;">Información del examen</h2>
            <form id="estiloEduardo.css" class="form-control-1"  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" style="display: inline-block; text-align: left"> 
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="titulo" style="margin-right: 10px; margin-left: 25px">Título</label><br>
                    <input class="form-control" type="text" id="titulo" name="titulo" required style="margin-left: 25px; width: calc(100% - 75px);"><br>
                </div>
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="edición" style="margin-right: 10px; margin-left: 25px">Edición</label><br>
                    <input class="form-control" type="text" id="edición" name="edición" min="0" pattern="\d+" style="margin-left: 25px;width: calc(100% - 75px); " title="Por favor, ingrese solo números enteros positivos" value="<?php echo date('Y'); ?>">
                </div><br>
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="fecha_activacion" style="margin-right: 10px; margin-left: 25px">Fecha de Activación</label><br>
                    <input class="form-control" type="date" id="fecha_activacion" name="fecha_activacion" required style="margin-left: 25px; width: calc(100% - 75px);"><br>
                </div>
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="fecha_final" style="margin-right: 10px; margin-left: 25px">Fecha Final</label><br>
                    <input class="form-control" type="date" id="fecha_final" name="fecha_final" required style="margin-left: 25px; width: calc(100% - 75px);"><br>
                </div>
                <div style="margin-bottom: 20px">    
                    <label class="form-label mt-3" for="num_examen" style="margin-right: 10px; margin-left: 25px">Número de Reactivos</label>
                    <input class="form-control" type="number" id="num_examen" name="num_examen" required min="0" max="100" value="0" style="margin-left: 25px; width: calc(100% - 75px);"><br>
                </div>
                <div id="archivo_pdf_container" style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="archivo_pdf" style="margin-right: 10px; margin-left: 25px">Subir Examen en PDF</label><br>
                    <input class="form-control-pdf" type="file" id="archivo_pdf" name="archivo_pdf" accept=".pdf" style="background-color: white;" required><br>
                </div>
                <div style="text-align: center;">
                    <input type="submit" value="Alta examen" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" style="margin-right: 30px;">
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