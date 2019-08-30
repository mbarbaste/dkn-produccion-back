<?php 

function getGrupos() {

    $conn = new mysqli('localhost', 'root', 'dr0p3smb', 'dolkin-prod');

    $query = "SELECT * FROM articulos_grupos_nuevo";

    //echo $query."\n\n";
   
    $result = $conn->query($query);

    $data[] = "";

    while($row = $result->fetch_assoc()) {
        $key = $row['pieza'];
        $value = $row['grupo'];
        $data[$key] = $value;
    }

    $conn->close();

    return $data;
}

function actualizar($articulo, $grupo) {

    $conn = new mysqli('localhost', 'root', 'dr0p3smb', 'dolkin-prod');
    
    //$query = "UPDATE articulos SET grupo = '".$grupo."' WHERE articulo='".$articulo."' LIMIT 1";
    //$query = "UPDATE orden_fabricacion SET grupo = '".$grupo."' WHERE articulo='".$articulo."'";
    $query = "UPDATE carga_horno_alta SET grupo = '".$grupo."' WHERE articulo='".$articulo."'";

    //echo $query."\n\n";
   
    $conn->query($query);

    $conn->close();
}

$mysqli = new mysqli('localhost', 'root', 'dr0p3smb', 'dolkin-prod');

if($mysqli->connect_error){
    die('Error en la conexion' . $mysqli->connect_error);
}

$grupos = getGrupos();

// $sql = "SELECT * FROM articulos";
//$sql = "SELECT * FROM orden_fabricacion";
//$sql = "SELECT * FROM carga_formacion";
//$sql = "SELECT * FROM carga_bizcocho";
//$sql = "SELECT articulo FROM carga_horno_alta";
$sql = "SELECT articulo FROM carga_horno_alta";

$resultado = $mysqli->query($sql);

$count = 0;

while($row = $resultado->fetch_assoc()) {

    $articulo = $row['articulo'];
    $pieza = substr($row['articulo'],-2,2);

    if (array_key_exists($pieza, $grupos)) {
        $grupo = $grupos[$pieza];
    } else {
        $grupo = "--";
    }

    // echo "Articulo: ".$articulo." - (".$pieza.") - Grupo:".$grupo."\n";
    
    actualizar($articulo, $grupo);

    $count++;
    // if ($count > 50) {
    //     die('Fin de Prueba');
    // }
}

print "Registros Procesados: ".(string)$count."\n\n"; 