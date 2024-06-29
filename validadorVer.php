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
        
        <h1 style="text-align: center;">Ver Validadores</h1>
        <h2 style="text-align: center;">Datos de los validadores registrados</h2>

        <div style="margin-top: 5px; text-align: center;">
            <form action="menuAdmin.php" method="post">
                <input type="submit" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" value="Regreso">
            </form>
        </div>

        <div class="table-container">
            <?php
            //comite_rol={2=validador, 3=revisor, 1=concursante, 0=administrador}
            $query = "SELECT comite_nombre, comite_edicion, comite_correo FROM comite WHERE comite_rol = 2 ORDER BY comite_edicion, comite_nombre";
            $result = mysqli_query($db_connection, $query);

            if (mysqli_num_rows($result) > 0) {
                echo "<table border='1' style='width: 100%; background-color: white;'>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Edición</th>
                                <th>Correo</th>
                            </tr>
                        </thead>
                        <tbody>";
                
                // Itera sobre los resultados y mostrar cada usuario en una fila de la tabla
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['comite_nombre'] . "</td>";
                    echo "<td>" . $row['comite_edicion'] . "</td>";
                    echo "<td>" . $row['comite_correo'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";

                // Verifica si hay muchos validadores y muestra el botón de regreso
                if (mysqli_num_rows($result) > 15) {
                    echo "<div style='margin-top: 5px; text-align: center;'>
                            <form action='menuAdmin.php' method='post'>
                                <input type='submit' class='w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large' value='Regreso'>
                            </form>
                          </div><br><br><br>";
                    }
                } else {
                echo "No se encontraron validadores.";
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
