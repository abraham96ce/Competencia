<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <?php 
        include 'cabecera.php';
        require_once "/srv/www/competencia/registro/config/conexion_db.php";

        if(!session_start()){
            header("Location: https://competencia.mat.uson.mx/registro");
        }
    ?>

    <body style="background-color: #b9b9bd">
        <div class="cabecera">
            <?php include 'titulo.php';?>
        </div><br>
        
        <h1>Ver Revisores</h1>
        <h2>Datos de los revisores registrados</h2>

        <div style="margin-top: 5px; text-align: center;">
            <form action="menuAdmin.php" method="post">
                <input type="submit" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" value="Regreso">
            </form>
        </div>

        <div class="table-container">
            <?php
            $query = "SELECT comite.comite_nombre AS Nombre, usuario.usuario_nombre AS Correo, usuario.usuario_edicion AS Edicion, examen.examen_titulo AS Examen, reactivo_exam.reactivo_n AS Reactivo FROM usuario 
                        LEFT JOIN comite ON (usuario.usuario_nombre=comite.comite_correo AND comite.comite_edicion=usuario.usuario_edicion) 
                        LEFT JOIN reactivo_revisor ON (reactivo_revisor.revisor_id=usuario.usuario_id) 
                        LEFT JOIN reactivo_exam ON (reactivo_exam.reactivo_id=reactivo_revisor.reactivo_id) 
                        LEFT JOIN examen ON (examen.examen_id=reactivo_exam.examen_id) WHERE comite.comite_rol=3 
                        ORDER BY Edicion ASC, Examen DESC, Reactivo";
            $result = mysqli_query($db_connection, $query);
            
            if (mysqli_num_rows($result) > 0) {
                echo "<table border='1' style='width: 100%; background-color: white;'>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Edición</th>
                                <th>Examen</th>
                                <th>Reactivo</th>
                            </tr>
                        </thead>
                        <tbody>";
            
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['Nombre'] . "</td>";
                    echo "<td>" . $row['Correo'] . "</td>";
                    echo "<td>" . $row['Edicion'] . "</td>";
                    echo "<td>" . ($row['Examen'] ?: "Sin asignar") . "</td>";
                    echo "<td>" . ($row['Reactivo'] ?: "Sin asignar") . "</td>";
                    echo "</tr>";
                }
            
                echo "</tbody></table>";
            } else {
                echo "No se encontraron revisores.";
            }
            
            mysqli_free_result($result);
            mysqli_close($db_connection);
            ?>
        </div><br><br>

        <div class="footer">
            <?php include 'piePagina.php';?>
        </div>   
    </body>
</html>
