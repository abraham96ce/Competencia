<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 
    include 'cabecera.php';
    require_once "/srv/www/competencia/registro/config/conexion_db.php";
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
       // Inicializa la variable de sesión para el aviso de puntaje
        if (!isset($_SESSION['datos_puntaje_guardados'])) {
            $_SESSION['datos_puntaje_guardados'] = false;
        }

        // Verificar si se ha enviado el formulario
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $examen_id = $_POST['asg_examen'];
            $reactivo_n = $_POST['reactivo_n'];
            $puntaje = $_POST['puntaje'];

            // Consulta SQL para verificar si el reactivo ya existe en el examen
            $query_verificarReactivo = "SELECT reactivo_id FROM reactivo_exam WHERE examen_id = '$examen_id' AND reactivo_n = '$reactivo_n'";
            $result_verificarReactivo = mysqli_query($db_connection, $query_verificarReactivo);

            if (mysqli_num_rows($result_verificarReactivo) > 0) {
                // Si el reactivo existe, actualiza el puntaje
                $row_verificarReactivo = mysqli_fetch_assoc($result_verificarReactivo);
                $reactivo_id = $row_verificarReactivo['reactivo_id'];

                $query_actualizarPuntaje = "UPDATE reactivo_exam SET puntos_posibles = '$puntaje' WHERE reactivo_id = '$reactivo_id'";

                if (mysqli_query($db_connection, $query_actualizarPuntaje)) {
                    $_SESSION['datos_puntaje_guardados'] = true;
                    // Actualiza el puntaje total del examen
                    $query_actualizarTotalPuntaje = "UPDATE examen SET examen_n_puntos = (SELECT SUM(puntos_posibles) FROM reactivo_exam WHERE examen_id = '$examen_id') WHERE examen_id = '$examen_id'";
                    mysqli_query($db_connection, $query_actualizarTotalPuntaje);
                } else {
                    echo "Error al actualizar el puntaje: " . mysqli_error($db_connection);
                    $_SESSION['datos_puntaje_guardados'] = false;
                }
            } else {
                $mensaje_error = "¡El reactivo no existe en este examen!";
            }
        }

        

        // Consulta SQL para obtener la lista de exámenes disponibles según el año seleccionado
        $año_seleccionado = isset($_POST['year']) ? $_POST['year'] : date('Y');
        $query_obtenerExamenes = "SELECT * FROM examen WHERE examen_edicion = '$año_seleccionado'";
        $result = mysqli_query($db_connection, $query_obtenerExamenes);

        // Consulta SQL para obtener los años con competencia ordenados cronológicamente
        $query_obtenerAñosCompetencia = "SELECT DISTINCT examen_edicion FROM examen ORDER BY examen_edicion DESC";
        $result_años_competencia = mysqli_query($db_connection, $query_obtenerAñosCompetencia);
        
        // Obtiene el año actual
        $año_actual = date("Y");

        // Bloque de código PHP para manejar la solicitud XMLHttpRequest
        if(isset($_POST['year'])) {
            // Obtiene el año seleccionado desde la solicitud
            $año_seleccionado = $_POST['year'];

            // Consulta SQL para obtener la lista de exámenes disponibles según el año seleccionado
            $query_obtenerExamenes = "SELECT * FROM examen WHERE examen_edicion = '$año_seleccionado'";
            $result = mysqli_query($db_connection, $query_obtenerExamenes);

            // Genera las opciones del campo de selección de exámenes
            $options = '';
            while($row = mysqli_fetch_assoc($result)) {
                $options .= "<option value='" . $row['examen_id'] . "'>" . $row['examen_titulo'] . "</option>";
            }

            // Devuelve las opciones del campo de selección de exámenes como respuesta a la solicitud
            echo $options;
            exit; // Termina el script después de enviar la respuesta
        }

        // Bloque de código PHP para manejar la solicitud XMLHttpRequest para obtener el número de reactivos y los puntajes actuales
        if(isset($_POST['examen_id'])) {
            $examen_id = $_POST['examen_id'];

            // Consulta SQL para obtener el número de reactivos del examen seleccionado
            $query_obtenerReactivos = "SELECT examen_n_reactivos FROM examen WHERE examen_id = '$examen_id'";
            $result = mysqli_query($db_connection, $query_obtenerReactivos);

            if($result) {
                $row = mysqli_fetch_assoc($result);
                $num_reactivos = $row['examen_n_reactivos'];
                $options = '';

                // Obtener los puntajes actuales de los reactivos del examen seleccionado
                $query_obtenerPuntajes = "SELECT reactivo_n, puntos_posibles FROM reactivo_exam WHERE examen_id = '$examen_id'";
                $result_puntajes = mysqli_query($db_connection, $query_obtenerPuntajes);
                $puntajes_actuales = array();

                if($result_puntajes) {
                    while($row_puntajes = mysqli_fetch_assoc($result_puntajes)) {
                        $reactivo_n = $row_puntajes['reactivo_n'];
                        $puntos_posibles = $row_puntajes['puntos_posibles'];
                        $puntajes_actuales[$reactivo_n] = $puntos_posibles;
                    }
                }

                // Generar las opciones del campo de selección de reactivos y agregar los puntajes actuales
                for($i = 1; $i <= $num_reactivos; $i++) {
                    $puntaje_actual = isset($puntajes_actuales[$i]) ? $puntajes_actuales[$i] : "No asignado";
                    $options .= "<option value='$i'>$i - Puntaje Actual: $puntaje_actual</option>";
                }

                // Devuelve las opciones del campo de selección de reactivos junto con los puntajes actuales como respuesta a la solicitud
                echo $options;
            } else {
                // Si hay un error en la consulta, muestra un mensaje de error
                echo "Error al obtener los reactivos del examen.";
            }

            exit; // Termina el script después de enviar la respuesta
        }

    ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var selectEdicion = document.getElementById("select_edicion");
            var selectExamen = document.getElementById("num_examen");
            var reactivoSelect = document.getElementById("reactivo_n");
            var puntajesContainer = document.getElementById("puntajes_actuales");

            selectEdicion.addEventListener("change", function () {
                var selectedYear = selectEdicion.value;
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        selectExamen.innerHTML = "<option value='' selected disabled>Seleccionar Examen</option>" + xhr.responseText;
                        // Después de actualizar la lista de exámenes, también debes actualizar la lista de reactivos
                        updateReactivos();
                    }
                };
                xhr.send("year=" + selectedYear);
            });

            selectExamen.addEventListener("change", function () {
                // Al seleccionar un examen, actualiza la lista de reactivos y los puntajes actuales
                updateReactivos();
                updatePuntajes();
            });

            function updatePuntajes() {
                var examenId = selectExamen.value;
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var puntajes = JSON.parse(xhr.responseText);
                        puntajesContainer.innerHTML = "<h3>Puntajes Actuales:</h3>";
                        for (var reactivo in puntajes) {
                            puntajesContainer.innerHTML += "<p>Reactivo " + reactivo + ": " + puntajes[reactivo] + "</p>";
                        }
                    }
                };
                xhr.send("examen_id=" + examenId);
            }

            function updateReactivos() {
                var examenId = selectExamen.value;
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        reactivoSelect.innerHTML = "<option value='' selected disabled>Seleccionar Reactivo</option>" + xhr.responseText;
                    }
                };
                xhr.send("examen_id=" + examenId);
            }

            // Llama a updateReactivos al cargar la página para asegurarse de que la lista de reactivos se muestre correctamente desde el principio
            updateReactivos();
        });
    </script>

    <div style="max-width: 700px; margin: 0 auto;">
        <div style="border: 1px solid #dddddd; padding: 20px; background-color: #f5f5f5; border-radius: 20px; box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75);">
       
        <?php if ($_SESSION['datos_puntaje_guardados']) : ?>
            <h6 style="text-align: center; font-size: 14px; color: red;">¡Puntaje registrado correctamente!</h6>
        <?php endif; ?>

        <?php if(isset($mensaje_error)): ?>
            <div style="text-align: center; font-size: 14 px; color: red;"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <h1 style="text-align: center">Formulario para Asignar puntaje</h1>

        <h2 style="margin-left: 30px; margin-bottom: -10px;">Información de los puntajes</h2>

            <form id="estiloEduardo.css" class="form-control-1" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" style="display: inline-block; text-align: left"> 
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="edición" style="margin-right: 10px; margin-left: 25px">Edición</label><br>
                    <select class="form-control" id="select_edicion" name="edición" style="margin-left: 25px; width: calc(100% - 49px);" required>
                        <option value="" selected disabled>Seleccionar Edición</option>
                            <?php 
                                // Muestra las opciones de los años con competencia
                                while($row_año_competencia = mysqli_fetch_assoc($result_años_competencia)) {
                                    $año_competencia = $row_año_competencia['examen_edicion'];
                                    echo "<option value='$año_competencia'>$año_competencia</option>";
                                }
                            ?>
                    </select><br>
                </div>
                
                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="examen" style="margin-right: 10px; margin-left: 25px">Examen</label><br>
                    <select class="form-control" id="num_examen" name="asg_examen" style="margin-left: 25px; width: calc(100% - 49px);" required>
                        <option value="" selected disabled>Seleccionar Examen</option>
                            <?php
                                // Muestra las opciones de los exámenes disponibles solo si hay una edición seleccionada
                                if (isset($_POST['year'])) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . $row['examen_id'] . "'>" . $row['examen_titulo'] . "</option>";
                                    }
                                }
                            ?>
                    </select><br>
                </div>

                <div style="margin-bottom: 20px">    
                    <label class="form-label mt-3" for="reactivo_n" style="margin-right: 10px; margin-left: 25px">Número de Reactivos</label>
                    <select class="form-control" id="reactivo_n" name="reactivo_n" required style="margin-left: 25px; width: calc(100% - 49px);">
                        <option value="" selected disabled>Seleccionar Reactivo</option>    
                    </select><br>
                </div>

                <div style="margin-bottom: 20px">    
                    <label class="form-label mt-3" for="puntaje" style="margin-right: 10px; margin-left: 25px">Puntaje</label>
                    <input class="form-control" type="number" id="puntaje" name="puntaje" required min="0" max="100" value="0" style="margin-left: 25px; width: calc(100% - 49px);"><br>
                </div>

                <div id="puntajes_actuales">

                </div>

                <div style="text-align: center;">
                    <input type="submit" value="Asignar puntaje" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" style="margin-right: 30px;">
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