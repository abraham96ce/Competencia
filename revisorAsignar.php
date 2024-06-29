<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 
    include 'cabecera.php';
    require_once "/srv/www/competencia/registro/config/conexion_db.php";
?>

<body>
    <?php
		if(!session_start()){
            header("Location: https://competencia.mat.uson.mx/registro");
        }
	?>

    <div class="cabecera">
        <?php include 'titulo.php';?>
    </div><br><br>

    <?php
        // Verifica si se ha enviado el formulario
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Procesa los datos del formulario
            $revisor = $_POST['asg_revisor'];
            $examen = $_POST['asg_examen'];
            $reactivo = $_POST['reactivo_n'];

            //Buscar los ID de cada elemento de la tabla a insertar.
            //revisor_ID = Buscar en la tabla "usuario" el campo "usuario_id" con las condiciones del anio, correo, nombre (del formulario).
            //$query_revisor_ID = "SELECT usuario_id FROM usuario where usuario.nombre=$correo and reactivo_n=$reactivo;
            // Construye la consulta con concatenación adecuada
            $query_revisor_ID = "SELECT usuario.usuario_id 
                                FROM usuario 
                                INNER JOIN comite 
                                ON comite.comite_nombre='$revisor' 
                                AND comite.comite_edicion=usuario.usuario_edicion 
                                AND comite.comite_correo=usuario.usuario_nombre";
            $result_revisor_ID = mysqli_query($db_connection, $query_revisor_ID);

            if($result_revisor_ID){
                //Obtiene la fila del conjunto de resultados
                $row = mysqli_fetch_assoc($result_revisor_ID);

                //Accede al usuario_id
                $revisor_ID = $row['usuario_id'];
            } else {
                // Muestra un mensaje de error en caso de fallo en la insercion
                $mensaje_confirmacion = "Error al asignar el Revisor al examen y reactivo!";
            }


            //reactivo_ID = Buscar en la tabla "reactivo_exam" el campo "reactivo_id" con las condiciones del examen y del reactivo de dicho examen
            //$query_reactivo_ID = "SELECT reactivo_id from reactivo_exam where examen_id=$examen and reactivo_n=$reactivo;

            $query_reactivo_ID = "SELECT reactivo_exam.reactivo_id 
                                FROM reactivo_exam 
                                WHERE reactivo_exam.examen_id=$examen AND reactivo_exam.reactivo_n=$reactivo";
            $result_reactivo_ID = mysqli_query($db_connection, $query_reactivo_ID);

            if($result_reactivo_ID){
                //Obtiene la fila del conjunto de resultados
                $row = mysqli_fetch_assoc($result_reactivo_ID);

                //Accede al usuario_id
                $reactivo_ID = $row['reactivo_id'];
            } else {
                // Muestra un mensaje de error en caso de fallo en la inserción
                $mensaje_confirmacion = "Error al asignar el revisor al examen y Reactivo!";
            }

            // Obtener el nombre del examen
            $query_examen_nombre = "SELECT examen_titulo FROM examen WHERE examen_id = $examen";
            $result_examen_nombre = mysqli_query($db_connection, $query_examen_nombre);

            if ($result_examen_nombre) {
                $row = mysqli_fetch_assoc($result_examen_nombre);
                $examen_nombre = $row['examen_titulo'];
            } else {
                $examen_nombre = "desconocido";
            }

            // Verifica si ya existe una asignación para el revisor y el reactivo
            $query_verificar_asignacion = "SELECT * 
                                   FROM reactivo_revisor 
                                   INNER JOIN reactivo_exam 
                                   ON reactivo_revisor.reactivo_id = reactivo_exam.reactivo_id 
                                   WHERE revisor_id = '$revisor_ID' 
                                   AND reactivo_revisor.reactivo_id = '$reactivo_ID' 
                                   AND reactivo_exam.examen_id = $examen";
            $result_verificar_asignacion = mysqli_query($db_connection, $query_verificar_asignacion);

            if(mysqli_num_rows($result_verificar_asignacion) > 0) {
                // Si ya existe la asignación, muestra un mensaje de error
                $mensaje_confirmacion = "¡El revisor ya está asignado a este reactivo!";
            } else {
                // Si no existe la asignación, procede a realizar la inserción
                // Consulta SQL para insertar los datos en la tabla reactivo_revisor
                $query_insertar = "INSERT INTO reactivo_revisor (revisor_id, reactivo_id) VALUES ('$revisor_ID', '$reactivo_ID')";

                // Ejecuta la consulta
                $result_insertar = mysqli_query($db_connection, $query_insertar);

                // Verifica si la inserción fue exitosa
                if($result_insertar) {
                    // Muestra un mensaje de confirmación
                    $mensaje_confirmacion = "¡El revisor $revisor ha sido asignado al $examen_nombre y al reactivo $reactivo correctamente!";
                } else {
                    // Muestra un mensaje de error en caso de fallo en la inserción
                    $mensaje_confirmacion = "¡Error al asignar el Revisor($revisor_ID) al examen y Reactivo($reactivo_ID)!";
                }
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

        if(isset($_POST['edicion'])) {
            $año_seleccionado = $_POST['edicion'];
        
            // Consulta SQL para obtener los revisores disponibles para la edición seleccionada
            $query_obtenerRevisores = "SELECT comite_nombre FROM comite WHERE comite_rol = 3 AND comite_edicion = '$año_seleccionado'";
            $result_revisores = mysqli_query($db_connection, $query_obtenerRevisores);
        
            // Genera las opciones del campo de selección de revisores
            $options = '';
            while ($row = mysqli_fetch_assoc($result_revisores)) {
                $options .= "<option value='" . $row['comite_nombre'] . "'>" . $row['comite_nombre'] . "</option>";
            }
        
            // Devuelve las opciones del campo de selección de revisores como respuesta a la solicitud
            echo $options;
            exit; // Termina el script después de enviar la respuesta
        }

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

        // Bloque de c�digo PHP para manejar la solicitud XMLHttpRequest para obtener el n�mero de reactivos
        if(isset($_POST['examen_id'])) {
            $examen_id = $_POST['examen_id'];
    
            // Consulta SQL para obtener el n�mero de reactivos del examen seleccionado
            $query_obtenerReactivos = "SELECT examen_n_reactivos FROM examen WHERE examen_id = '$examen_id'";
            $result = mysqli_query($db_connection, $query_obtenerReactivos);
    
            if($result) {
                $row = mysqli_fetch_assoc($result);
                $num_reactivos = $row['examen_n_reactivos'];
                $options = '';
    
                for($i = 1; $i <= $num_reactivos; $i++) {
                    $options .= "<option value='$i'>$i</option>";
                }
    
                // Devuelve las opciones del campo de selecci�n de reactivos como respuesta a la solicitud
                echo $options;
            } else {
                // Si hay un error en la consulta, muestra un mensaje de error
                echo "¡Error al obtener los reactivos del examen!";
            }
    
            exit; // Termina el script después de enviar la respuesta
        }    
    ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var selectEdicion = document.getElementById("select_edicion");
            var selectRevisor = document.getElementById("selectRevisor");
            var selectExamen = document.getElementById("num_examen");
            var reactivoSelect = document.getElementById("reactivo_n");

            function updateRevisores() {
                var selectedYear = selectEdicion.value;

                // Realiza una solicitud AJAX para obtener los revisores disponibles para la edición seleccionada
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        selectRevisor.innerHTML = "<option value='' selected disabled>Seleccionar Revisor</option>" + xhr.responseText;
                    }
                };
                xhr.send("edicion=" + selectedYear);
            }

            selectEdicion.addEventListener("change", function () {
                // Al cambiar la edición, actualiza la lista de revisores
                updateRevisores();
            });

            // Llama a updateRevisores al cargar la página para asegurarse de que la lista de revisores se muestre correctamente desde el principio
            updateRevisores();

            selectEdicion.addEventListener("change", function () {
                var selectedYear = selectEdicion.value;
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        selectExamen.innerHTML = "<option value='' selected disabled>Seleccionar Examen</option>" + xhr.responseText;
                        // Despu�s de actualizar la lista de ex�menes, tambi�n debes actualizar la lista de reactivos
                        updateReactivos();
                    }
                };
                xhr.send("year=" + selectedYear);
            });

            selectExamen.addEventListener("change", function () {
                // Al seleccionar un examen, actualiza la lista de reactivos
                updateReactivos();
            });

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
            <?php if (isset($mensaje_confirmacion)): ?>
                <p style="color: red;"><?php echo $mensaje_confirmacion; ?></p>
            <?php endif; ?>

            <h1 style="text-align: center">Asignar Revisores</h1>

            <h2 style="margin-left: 30px; margin-bottom: -10px;">Datos para asignar revisores</h2><br>

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
                    <label class="form-label mt-3" for="revisor" style="margin-right: 10px; margin-left: 25px">Revisor</label><br>
                    <select class="form-control" id="selectRevisor" name="asg_revisor" style="margin-left: 25px; width: calc(100% - 49px);" required>
                        <option value="" selected disabled>Seleccionar Revisor</option>
                            <?php
                            // Consulta SQL para obtener los revisores con el rol 3
                            $query_obtenerRevisores = "SELECT comite_nombre FROM comite WHERE comite_rol = 3";
                            $result_revisores = mysqli_query($db_connection, $query_obtenerRevisores);

                            // Genera las opciones del campo de selección de revisores
                            while ($row = mysqli_fetch_assoc($result_revisores)) {
                                echo "<option value='" . $row['comite_nombre'] . "'>" . $row['comite_nombre'] . "</option>";
                            }
                            ?>
                    </select><br>
                </div>

                <div style="margin-bottom: 20px">
                    <label class="form-label mt-3" for="examen" style="margin-right: 10px; margin-left: 25px">Examen</label><br>
                    <select class="form-control" id="num_examen" name="asg_examen" style="margin-left: 25px; width: calc(100% - 49px);" required>
                        <option value="" selected disabled>Seleccionar Examen</option>
                            <?php
                                // Muestra las opciones de los examenes disponibles solo si hay una edición seleccionada
                                if (isset($_POST['year'])) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . $row['examen_id'] . "'>" . $row['examen_titulo'] . "</option>";
                                    }
                                }
                            ?>
                    </select><br>
                </div>

                <div style="margin-bottom: 20px">    
                    <label class="form-label mt-3" for="reactivo_n" style="margin-right: 10px; margin-left: 25px">Número de Reactivo</label>
                    <select class="form-control" id="reactivo_n" name="reactivo_n" required style="margin-left: 25px; width: calc(100% - 49px);">
                        <option value="" selected disabled>Seleccionar Reactivo</option>
                    </select><br>
                </div>  

                <div style="text-align: center;">
                    <input type="submit" value="Asignar revisor" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" style="margin-right: 30px;">
                    <div style="display: inline-block;">
                        <a href="menuAdmin.php" style="text-decoration: none;"><input type="button" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" value="Regreso" value="Regreso"></a>
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