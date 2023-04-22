<?php
error_reporting(0);
include_once "./vendor/autoload.php";
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
session_start();
if (isset($_POST['import'])) {
    if ($_POST['import'] == "csv" and $_FILES["csv"]["name"] != "" and !empty($_POST['impre'])) {
        $info = new SplFileInfo($_FILES["csv"]["name"]);

        if ($info->getExtension() == 'csv') {
            $_SESSION["impresora"] = $_POST['impre'];
            $carpeta               = "./csv";
            if (!file_exists($carpeta)) {
                mkdir($carpeta, 0777, true);
                chmod($carpeta, 0777);
            }

            $nom  = $_FILES["csv"]["name"];
            $ruta = $_FILES["csv"]["tmp_name"];
            if (move_uploaded_file($ruta, $carpeta . "/" . $nom)) {
                $registros = array();
                if (($fichero = fopen($carpeta . "/" . $nom, "r")) !== false) {
                    // Lee los nombres de los campos
                    $nombres_campos = fgetcsv($fichero, 0, $_POST['separador'], "\"", "\"");
                    $num_campos     = count($nombres_campos);
                    // Lee los registros
                    while (($datos = fgetcsv($fichero, 0, $_POST['separador'], "\"", "\"")) !== false) {
                        // Crea un array asociativo con los nombres y valores de los campos
                        for ($icampo = 0; $icampo < $num_campos; $icampo++) {
                            $registro[$nombres_campos[$icampo]] = $datos[$icampo];
                        }
                        // Añade el registro leido al array de registros
                        $registros[] = $registro;
                    }
                    fclose($fichero);

                    for ($i = 0; $i < count($registros); $i++) {
                        try {
                            $connector = new WindowsPrintConnector($_POST['impre']);

                            $printer = new Printer($connector);
                            $printer->setPrintLeftMargin(4);
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            for ($icampo = 0; $icampo < $num_campos; $icampo++) {

                                if ($icampo == 0) {
                                    $printer->text($registros[$i][$nombres_campos[$icampo]] . "\n");
                                } else if ($icampo == 1) {
                                    $printer->text("SKU: " . $registros[$i][$nombres_campos[$icampo]] . "\n");
                                } else if ($icampo == 2) {
                                    $printer->text("Precio: S/. " . $registros[$i][$nombres_campos[$icampo]]);
                                }

                            }
                            if (!empty($_SESSION["impre"])){
                              if($_SESSION["impre"] == 6) {
                                  $_SESSION["impre"] = 7;
                              } else {
                                  $_SESSION["impre"] = 6;
                              }
                            }else{
                              $_SESSION["impre"] = 6;
                            }

                            for ($ii = 0; $ii < $_SESSION["impre"]; $ii++) {
                                $printer->text("\n");
                            }

                            $printer->cut(Printer::CUT_FULL);
                            $printer->close();

                        } catch (Exception $e) {
                            $error = 1;
                        }
                    }
                    unlink($carpeta . "/" . $nom);
                    if(isset($error)){
                      echo "<script> alert('No se pudo imprimir en esta impresora: " . $_SESSION["impresora"] ."');</script>";
                    }
                }
            }
        }

    }
} else {
    if (!empty($_POST['precio']) and !empty($_POST['impre']) and !empty($_POST['nombre']) and !empty($_POST['sku'])) {

        try {
            $_SESSION["impresora"] = $_POST['impre'];
            $connector             = new WindowsPrintConnector($_POST['impre']);

            $printer = new Printer($connector);
            $printer->setPrintLeftMargin(4);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($_POST['nombre'] . "\n");
            $printer->text("SKU: " . $_POST['sku'] . "\n");
            $printer->text("Precio: S/. " . $_POST['precio']);
            if (!empty($_SESSION["impre"])){
              if($_SESSION["impre"] == 6) {
                  $_SESSION["impre"] = 7;
               } else {
                  $_SESSION["impre"] = 6;
              }
            }else{
              $_SESSION["impre"] = 6;
            }

            for ($i = 0; $i < $_SESSION["impre"]; $i++) {
                $printer->text("\n");
            }

            $printer->cut(Printer::CUT_FULL);
            $printer->close();

        } catch (Exception $e) {
            echo "<script> alert('No se pudo imprimir en esta impresora: " . $_SESSION["impresora"] ."');</script>";
        }

    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Test Impresora Termica</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./css/bootstrap.min.css">
  <script src="./js/jquery.min.js"></script>
  <script src="./js/popper.min.js"></script>
  <script src="./js/bootstrap.min.js"></script>
  <style type="text/css">
    .bg-light{
      background: #F8F9FA;
    }
    .col{
      width: 700px;

    }
    /*@media screen and (min-width: 600px) {
      .col {
        width: 600px;
      }
    }*/

    .tooltip1 {
      position: relative;
      display: inline-block;
    }

    .tooltip1 .tooltiptext1 {
      visibility: hidden;
      width: 340px;
      background-color: #5674F1;
      color: #fff;
      text-align: center;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 18px;
      text-align: justify;
      border-radius: 6px;
      padding: 10px 15px;
      position: absolute;
      line-height: 110%;
      z-index: 1000;
      bottom: 125%;
      left: 50%;
      margin-left: -170px;
      opacity: 0;
      transition: opacity 0.3s;
    }
    @media screen and (max-width: 400px) {
      .tooltip1 .tooltiptext1 {
        width: 250px;
        margin-left: -80px;
      }
    }

    .tooltip1 .tooltiptext1::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: #5674F1 transparent transparent transparent;
    }

    .tooltip1:hover .tooltiptext1 {
      visibility: visible;
      opacity: 1;
    }
  </style>
</head>
<body class="bg-light">

  <div class="container col">
  <h2>Impresora</h2>
    <p>Pruducto, Sku y Precio.</p>
    <form action="<?php $_SERVER['PHP_SELF'];?>" method="post" enctype="multipart/form-data" class="needs-validation form-horizontal" novalidate>
    <div class="form-group">
      <label for="impre">Nombre de la Impresora:</label>
      <input type="text" class="form-control" id="impre" placeholder="Escribir Impresora" name="impre" value="<?php echo $_SESSION['impresora']; ?>" required>
      <div class="valid-feedback">Valido.</div>
      <div class="invalid-feedback">Por favor complete este campo.</div>
    </div>
    <div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="ck" name="import" value="csv">
      <label class="custom-control-label" for="ck">Importar CSV.</label>
    </div>
    <div id="form"  style="display: block;">
      <div class="form-group">
        <label for="nombre">Nombre del Producto:</label>
        <input type="text" class="form-control" id="nombre" placeholder="Escribir Nombre" name="nombre" required>
        <div class="valid-feedback">Valido.</div>
        <div class="invalid-feedback">Por favor complete este campo.</div>
      </div>
      <div class="form-group">
        <label for="sku">SKU:</label>
        <input type="text" class="form-control" id="sku" placeholder="Escribir Sku" name="sku" required>
        <div class="valid-feedback">Valido.</div>
        <div class="invalid-feedback">Por favor complete este campo.</div>
      </div>
      <div class="form-group">
        <label for=precio">Precio:</label>
        <input type="text" class="form-control" id="precio" placeholder="Escribir Precio" name="precio" required>
        <div class="valid-feedback">Valido.</div>
        <div class="invalid-feedback">Por favor complete este campo.</div>
      </div>
    </div>
    <div id="import" style="display: none;">
      <p>Importar Archivo CVS:</p>
      <div class="custom-file mb-3">
        <input type="file" class="custom-file-input" id="customFile" name="csv" accept=".csv">
        <label class="custom-file-label" for="customFile">Seleccione CSV</label>
        <div class="valid-feedback">Valido.</div>
        <div class="invalid-feedback">Seleccione un archivo csv.</div>
      </div>
      <div class="custom-control custom-radio custom-control-inline">
        <input type="radio" class="custom-control-input" value="," id="coma" name="separador">
        <label class="custom-control-label tooltip1" id="c" for="coma">Coma (,)
        <span class="tooltiptext1">Nombre,SKU,Precio Productos,Codigos de sku,precios</span></label>
      </div>
      <div class="custom-control custom-radio custom-control-inline">
        <input type="radio" class="custom-control-input" value=";" name="separador" id="punto" checked>
        <label class="custom-control-label tooltip1" id="p" for="punto">Punto y Coma (;)
        <span class="tooltiptext1">Nombre;SKU;Precio Productos;Codigos de sku;precios</span></label>
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top: 30px;">Imprimir</button>
  </form>
</div>
<script>
// Disable form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Get the forms we want to add validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);

  $("#ck").click(function(){
      if($('input[name="import"]:checked').val()=="csv"){
        $("#import").css("display","block");
        $("#form").css("display","none");
        $("#customFile").attr("required", "required");
        $("#nombre").removeAttr("required");
        $("#sku").removeAttr("required");
        $("#precio").removeAttr("required");
      }else{
        $("#import").css("display","none");
        $("#form").css("display","block");
        $("#customFile").removeAttr("required");
        $("#nombre").attr("required", "required");
        $("#sku").attr("required", "required");
        $("#precio").attr("required", "required");

      }
    });
  $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
  });
})();
</script>
</body>
</html>