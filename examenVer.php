<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 
    include 'cabecera.php';
    require_once "/srv/www/competencia/registro/config/conexion_db.php";
?>

<body style="background-color: #b9b9bd">
    <div class="cabecera">
        <?php include 'titulo.php';?>
    </div><br>
    
    <h1>Ver Exámenes</h1>
    <h2>Datos de los exámenes registrados</h2>

    <div style="margin-top: 5px; text-align: center;">
        <form action="menuAdmin.php" method="post">
            <input type="submit" class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large" value="Regreso">
        </form>
    </div>

    <div class="table-container">
        <?php
        $query = "SELECT examen_titulo, examen_edicion, examen_apertura, examen_cierre, examen_n_reactivos, examen_n_puntos, examen_archivo 
                    FROM examen 
                    ORDER BY examen_edicion, examen_apertura";
        $result = mysqli_query($db_connection, $query);

        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1' style='width: 100%; background-color: white;'>
                    <thead>
                        <tr>
                            <th>Nombre del Examen</th>
                            <th>Edición</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Reactivos</th>
                            <th>Puntos</th>
                        </tr>
                    </thead>
                    <tbody>";
            
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td><a href='https://competencia.mat.uson.mx/registro/examenes/" . $row['examen_edicion'] . "/" . $row['examen_archivo'] . "' target='_blank'>" . $row['examen_titulo'] . "</a></td>";

                        echo "<td>" . $row['examen_edicion'] . "</td>";
                        echo "<td>" . date('Y-m-d', strtotime($row['examen_apertura'])) . "</td>";
                        echo "<td>" . date('Y-m-d', strtotime($row['examen_cierre'])) . "</td>";
                        echo "<td>" . $row['examen_n_reactivos'] . "</td>";
                        echo "<td>" . $row['examen_n_puntos'] . "</td>";
                        echo "</tr>";
                    }
                    
            echo "</tbody></table>";

            // Muestra el botón de regreso si hay muchos exámenes
            if (mysqli_num_rows($result) > 15) {
                echo "<div style='margin-top: 5px; text-align: center;'>
                        <form action='menuAdmin.php' method='post'>
                            <input type='submit' class='w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large' value='Regreso'>
                        </form>
                      </div><br><br><br>";
            }
        } else {
            echo "No se encontraron exámenes.";
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

