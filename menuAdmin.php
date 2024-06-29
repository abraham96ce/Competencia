<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php include 'cabecera.php';?>

<body style="background-color: #b9b9bd">
    <div>
        <?php include 'titulo.php';?>
    </div><br><br>

    <div style="max-width: 700px; margin: 0 auto;">
        <div style="border: 1px solid #dddddd; padding: 20px; background-color: #f5f5f5; border-radius: 20px; box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75);">
        <h1>Menú de Administrador</h1>
        <div style="margin-bottom: 20px">
            <label class="form-label mt-3"for="sel_opción" style="margin-right: -300px; margin-left: -760px">Selecciona una opción:</label>
        </div>
            <div class="button-container">
                <div class="vertical-buttons" style="width: calc(50% - 10px);">
                    <h4> Validador </h4>
                    <a href="validadorAlta.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Alta de Validador </button></a><br>
                    <a href="validadorVer.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Ver Validadores </button></a><br>
                </div>
                <div class="vertical-buttons" style="width: calc(50% - 10px);">
                    <h4> Examen </h4>
                    <a href="examenAlta.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Alta de Examen </button></a><br>
                    <a href="examenPuntajeReactivo.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Asignar Puntaje de Examen</button></a><br>
                    <a href="examenVer.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Ver Examenes </button></a><br>
                </div>
                <div class="vertical-buttons" style="width: calc(50% - 10px);">
                    <h4> Revisor </h4>
                    <a href="revisorAlta.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Alta de Revisor </button></a><br>
                    <a href="revisorVer.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Ver Revisores </button></a><br>
                    <a href="revisorAsignar.php"><button class="w3-button w3-btn w3-ripple w3-theme-slightly-dark-red w3-round-large">Asignar Revisores </button></a><br>
                </div>
            </div>
        </div>
    </div>
    <br><br>

    <div class="footer">
        <?php include 'piePagina.php';?>
    </div>

</body>
</html>
