<?php 
session_start();
include("../config/conexion.php"); 

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $direccion = $_POST['direccion'];
    $ubicacion = $_POST['ubicacion'];
    $telefono = $_POST['telefono'];
    $whatsapp = $_POST['whatsapp'];
    $facebook = $_POST['facebook'];
    $correo = $_POST['correo'];
    $horarios = $_POST['horarios'];

    //existe informacion
    $sqlExiste = "SELECT COUNT(*) FROM info_contacto";
    $stmtExiste = $conexion->query($sqlExiste);
    $existe = $stmtExiste->fetchColumn();

    //si ya existe
    if($existe > 0){
        $sqlUpdate = "UPDATE info_contacto SET
        direccion = :direccion,
        ubicacion = :ubicacion,
        telefono = :telefono,
        whatsapp = :whatsapp,
        facebook = :facebook,
        correo = :correo,
        horarios = :horarios";

        $stmtUpdate = $conexion->prepare($sqlUpdate);

        //vincular variables
        $stmtUpdate->bindParam(':direccion', $direccion);
        $stmtUpdate->bindParam(':ubicacion', $ubicacion);
        $stmtUpdate->bindParam(':telefono', $telefono);
        $stmtUpdate->bindParam(':whatsapp', $whatsapp);
        $stmtUpdate->bindParam(':facebook', $facebook);
        $stmtUpdate->bindParam(':correo', $correo);
        $stmtUpdate->bindParam(':horarios', $horarios);

        $stmtUpdate->execute();
    }

    //si no existe INSERT
    else{
        $sqlInsert = "INSERT INTO info_contacto(direccion, ubicacion, telefono, whatsapp, facebook, correo, horarios)
        VALUES (:direccion, :ubicacion, :telefono, :whatsapp, :facebook, :correo, :horarios)";

        $stmtInsert = $conexion->prepare($sqlInsert);

        $stmtInsert->bindParam(':direccion', $direccion);
        $stmtInsert->bindParam(':ubicacion', $ubicacion);
        $stmtInsert->bindParam(':telefono', $telefono);
        $stmtInsert->bindParam(':whatsapp', $whatsapp);
        $stmtInsert->bindParam(':facebook', $facebook);
        $stmtInsert->bindParam(':correo', $correo);
        $stmtInsert->bindParam(':horarios', $horarios);

        $stmtInsert->execute();
    }

    $_SESSION['toast'] = 'Datos actualizados correctamente';
    $_SESSION['toast_tipo'] = 'success';

    header("Location: configuracion.php");
    exit;
}

$sql = "SELECT telefono, whatsapp, facebook, direccion, ubicacion, correo, horarios FROM info_contacto";
$stmt = $conexion->query($sql);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$info){

    $info = [
        'direccion' => '',
        'ubicacion' => '',
        'telefono' => '',
        'whatsapp' => '',
        'facebook' => '',
        'correo' => '',
        'horarios' => ''
    ];
}

include("includes/auth.php");
include("includes/header.php");
include("includes/sidebar.php");
?>

<div class="main">
    <h5 class="m-3">Información de contacto</h5>
    <div class="row">
        <div class="col-12 col-sm-12 col-md-8 col-lg-6 col-xl-5">
            <div class="container-fluid">

                <form action="configuracion.php" method="post">

                    <div class="mb-3">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" class="form-control" value="<?= $info['direccion']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="ubicacion">Ubicación google</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control" value="<?= $info['ubicacion']; ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control" value="<?= $info['telefono']; ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="whatsapp">WhatsApp</label>
                            <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="<?= $info['whatsapp']; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="facebook">Facebook</label>
                        <input type="text" id="facebook" name="facebook" class="form-control" value="<?= $info['facebook']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="correo">Gmail</label>
                        <input type="text" id="correo" name="correo" class="form-control" value="<?= $info['correo']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="horarios">Horarios</label>
                        <textarea name="horarios" id="horarios" class="form-control" rows="4"><?= $info['horarios']; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>