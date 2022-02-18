<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_reporting', E_ALL);

session_start();


if (file_exists("archivo.txt")) {
    //si existe el archivo lo decodifica para crear el array de clientes
    $archivo = file_get_contents("archivo.txt");

    $aClientes = json_decode($archivo, true);
} else {
    //sino crea un array vacio
    $aClientes = array();
}



if (isset($_GET["id"])) {
    //pregunta el id de la pagina (posicion del array)
    $id = $_GET["id"];
} else {
    $id = "";
}

if(isset($_GET["do"]) && $_GET["do"] == "copiar"){
    $aux = $aClientes[$id];
    $aClientes[] = $aux;
    $jsonClientes = json_encode($aClientes); 
    file_put_contents("archivo.txt",$jsonClientes);
    header("location: index.php");
}

if(isset($_GET["do"]) && $_GET["do"] == "undo"){    
    $undo = file_get_contents("undo.txt");
    file_put_contents("archivo.txt",$undo);
    header("location: index.php");
}
if(isset($_SESSION["mensaje"]) && $_SESSION["mensaje"] == "Se elimino correctamente"){
    
    session_destroy();
}

if(isset($_GET["do"]) && $_GET["do"] == "eliminar"){
    //undo
    $jsonClientes = json_encode($aClientes);
    file_put_contents("undo.txt",$jsonClientes);
    

    //elimina el registro del array y la imagen adjunta
    if(file_exists($aClientes[$id]["imagen"])){
    unlink($aClientes[$id]["imagen"]);
    }
    unset($aClientes[$id]);
    
    //sobre escribe el archivo con la posicion del array eliminada 
    $jsonClientes = json_encode($aClientes); 
    file_put_contents("archivo.txt",$jsonClientes);
    
    header("location: index.php");
    $_SESSION["mensaje"] ="Se elimino correctamente";    
}




if(isset($_GET["do"]) && $_GET["do"] == "nuevo"){
    session_destroy();
    header("location: index.php");
}

if ($_POST) {
    //tomo la informacion del formulario
    $dni = trim($_REQUEST["txtDni"]); //trim elimina los espacios en blanco cuando se cargan en el formulario
    $nombre = trim($_REQUEST["txtNombre"]);
    $telefono = trim($_REQUEST["txtTelefono"]);
    $correo = trim($_REQUEST["txtCorreo"]);

    //validar que la imagen se haya subido bien y guardo la ruta de la imagen para luego guardarla en el array

    if ($_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
        //si ya habia una imagen cargada la borra
         if(isset($aClientes[$id]["imagen"]) &&  $aClientes[$id]["imagen"] != ""){
            if(file_exists($aClientes[$id]["imagenes"])){
                unlink($aClientes[$id]["imagen"]);
                }
         }
        //carga la imagen del formulario y almacena la ruta 
        $nombreAleatorio = date("Ymdhm") . rand(1, 5000);
        $archivo_tmp = $_FILES["imagen"]["tmp_name"];
        $nombreArchivoImagen = $_FILES["imagen"]["name"];
        $extension = pathinfo($nombreArchivoImagen, PATHINFO_EXTENSION);
        if($extension == "png" || $extension == "jpeg" || $extension == "jpg"){
        move_uploaded_file($archivo_tmp, "imagenes/$nombreAleatorio.$extension");
        }
        $rutaImagen = "imagenes/$nombreAleatorio.$extension";
    }else{
        if($id >= 0){
         $rutaImagen = $aClientes[$id]["imagen"];
        }else{
            $rutaImagen = "";
        }
    }

    //crear un array con todos los datos
    if($id >= 0){
    $aClientes[$id] = array(
        "dni" => $dni,
        "nombre" => $nombre,
        "telefono" => $telefono,
        "correo" => $correo,
        "imagen" => $rutaImagen
    ); 
    $_SESSION["mensaje"] ="Se actualizo correctamente" ;
    
    session_destroy();
    }else{
        $aClientes[] = array(
            "dni" => $dni,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "correo" => $correo,
            "imagen" => $rutaImagen
        );
        $_SESSION["mensaje"] ="Se guardo correctamente" ;
        session_destroy();
    }
    


    //convertir el array a JSON
    $jsonClientes = json_encode($aClientes);

    //almacenar el JSON en archivo.txt     
    file_put_contents("archivo.txt",$jsonClientes);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>abmclientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/40e341f8f7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>
    <main class="container">
        <div class="row">
            <div class="col-12 text-center py-5">
                <h1>Registro de clientes</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-6 form-group">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div>
                        <label for="txtDni">DNI: *</label>
                        <input class="form-control my-2" type="text" name="txtDni" id="txtDni" required value="<?php echo isset($aClientes[$id]["dni"]) ? $aClientes[$id]["dni"] : ""; ?>">
                    </div>
                    <div>
                        <label for="txtNombre">Nombre: *</label>
                        <input class="form-control my-2" type="text" name="txtNombre" id="txtNombre" required value="<?php echo isset($aClientes[$id]["nombre"]) ? $aClientes[$id]["nombre"] : ""; ?>">
                    </div>
                    <div>
                        <label for="txtTelefono">Telefono: *</label>
                        <input class="form-control my-2" type="text" name="txtTelefono" id="txtTelefono" required value="<?php echo isset($aClientes[$id]["telefono"]) ? $aClientes[$id]["telefono"] : ""; ?>">
                    </div>
                    <div>
                        <label for="txtCorreo">Correo: *</label>
                        <input class="form-control my-2" type="text" name="txtCorreo" id="txtCorreo" required value="<?php echo isset($aClientes[$id]["correo"]) ? $aClientes[$id]["correo"] : ""; ?>">
                    </div>
                    <div>
                        <label for="imagen">Archivo adjunto:</label>
                        <input type="file" name="imagen" id="imagen" class="form-control" accept=".jpg, .jpeg, .png">
                        <small>archivos admitidos .jpg, .jpeg, .png </small>
                    </div>
                    <div>
                        <input type="submit" name="enviar" value="Enviar" class="btn bg-primary text-white">
                        <a href="?do=nuevo" class="btn btn-secondary">Nuevo</a>                        
                        <a href="?do=undo" class="btn btn-danger">Undo</a>
                        
                        
                        
                    </div>
                </form>
            </div>

            <div class="col-6">
                    <?php 
                        if(isset($_SESSION["mensaje"])){
                            echo $_SESSION["mensaje"] == "Se elimino correctamente"? '<div class="alert alert-danger" role="alert">' : ($_SESSION["mensaje"] == "Se actualizo correctamente"?'<div class="alert alert-warning" role="alert">'  : '<div class="alert alert-success" role="alert">');
                            echo $_SESSION["mensaje"]; 
                            echo "</div>";                            
                        } 
                        ?>
                    
                    <table class="table table-hover shadow border">
                    <thead>
                        <tr>
                            <th>Imagenes</th>
                            <th>DNI</th>
                            <th>Nombre</th>
                            <th>Telefono</th>
                            <th>Correo</th>
                            <th>Accion</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                       
                        foreach ($aClientes as $pos => $cliente) : ?>

                            <tr>
                                <td><img src="<?php echo $cliente["imagen"]; ?>" class="img-thumbnail"></td>
                                <td><?php echo $cliente["dni"]; ?></td>
                                <td><?php echo $cliente["nombre"]; ?> </td>
                                <td><?php echo $cliente["telefono"]; ?></td>
                                <td><?php echo $cliente["correo"]; ?></td>
                                <td>
                                    <a href="?id=<?php echo $pos?>"><i class="fas fa-edit"></i></a>
                                    <a href="?id=<?php echo $pos?>&do=eliminar"><i class="fas fa-trash borrar"></i></a>
                                    <a href="?id=<?php echo $pos?>&do=copiar"><i class="fa-solid fa-copy"></i></a>
                                </td>

                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </main>



</body>

</html>