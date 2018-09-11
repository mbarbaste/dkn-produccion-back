<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function checkArticulo($articulo) {

// Buscar si existe el código de artículo

    $sql = "SELECT * FROM `articulos` 
    WHERE articulo = '".$articulo."' LIMIT 1";

    try {
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        
        if ( $stmt->rowCount() == 0 ) {

            $db = null;
            return false;

        } else {
            
            $db = null;
            return true;
        }
            

    } catch(PDOException $e) {
        $db = null;
        return false;
    }
// Fin buscar articulo

}


$app->get('/api/ofab/{id}', function(Request $request, Response $response, $args){
    
        $id = $request->getAttribute('id');
    
        $sql = "SELECT * FROM `orden_fabricacion` 
                WHERE id = '".$id."' LIMIT 1";
    
       // echo json_encode(array('sql' => $sql));
    
        try {
            $db = new db();
            $db = $db->connect();
    
            $stmt = $db->query($sql);
            $respuesta = $stmt->fetchAll(PDO::FETCH_OBJ);           
    
        } catch(PDOException $e) {
            $respuesta = array('status' => 'failed', 'error' => $e->errorInfo[1]);
        }
        $db = null;
        header("Content-Type: application/json");
        echo json_encode($respuesta);
    });






$app->get('/api/ofab/{desde}/{hasta}/{modelo}/{maquina}/{estado}', function(Request $request, Response $response, $args){

    $desde = $request->getAttribute('desde');
    $hasta = $request->getAttribute('hasta');
    $modelo = $request->getAttribute('modelo');
    $maquina = $request->getAttribute('maquina');
    $estado = $request->getAttribute('estado');
    


    $qModelo = "";
    if($modelo == 'todos') {

    } else {
        $qModelo = " AND articulo LIKE '".$modelo."%' ";
    }

    $qMaquina = "";
    if($maquina == 'todas') {

    } else {
        $qMaquina = " AND maquina = '".$maquina."' ";
    }

    $qEstado = "";
    if($estado == "todos") { $qEstado = "";}
    if($estado == "proceso") { $qEstado = " AND fecha_fin IS NULL";}
    if($estado == "finalizados") { $qEstado = " AND fecha_fin > '0' ";}

    //$fecha = $args['fecha'];

    $sql = "SELECT * FROM `orden_fabricacion` 
            WHERE fecha_inicio>='".$desde."' AND  
            fecha_inicio<='".$hasta."'
            ".$qModelo.
            $qMaquina.$qEstado." ORDER by id DESC";

   //echo json_encode(array('sql' => $sql));

    try {
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $respuesta = $stmt->fetchAll(PDO::FETCH_OBJ);           

    } catch(PDOException $e) {
        $respuesta = array('status' => 'failed', 'error' => $e->errorInfo[1]);
    }
    $db = null;
    header("Content-Type: application/json");
    echo json_encode($respuesta);
});

$app->post('/api/ofab', function(Request $request, Response $response, $args){

    $articulo = $request->getParsedBody()['articulo'];
    $cantidad = $request->getParsedBody()['cantidad'];
    $maquina = $request->getParsedBody()['maquina'];
    $molde = $request->getParsedBody()['molde'];
    $fecha_inicio = $request->getParsedBody()['fecha_inicio'];
    $observaciones = $request->getParsedBody()['observaciones'];

    if( checkArticulo($articulo)) {

        $query = "INSERT INTO orden_fabricacion  
        (articulo, cantidad, maquina, molde, fecha_inicio, observaciones) 
        VALUES 
        (:articulo, :cantidad, :maquina, :molde, :fecha_inicio, :observaciones) ";   

try {
$db = new db();
$db = $db->connect();
    
$stmt = $db->prepare($query);

$stmt->bindParam(':articulo', $articulo);
$stmt->bindParam(':cantidad', $cantidad);
$stmt->bindParam(':maquina', $maquina);
$stmt->bindParam(':molde', $molde);
$stmt->bindParam(':fecha_inicio', $fecha_inicio);
$stmt->bindParam(':observaciones', $observaciones);

$stmt->execute();

$id = $db->lastInsertId();
$db = null;

if($id > 0) { 
    $respuesta = array('status' => 'ok', 'Insert ID' => $id, 'Orden de Fabricacion Agregada' => $id.' '.$articulo);
}

} catch (PDOException $e) {
if ($e->errorInfo[1] == 1062) {
    $respuesta = array('status' => 'failed', 'msg' => 'Ya existe la orden '.$id );
} else {
    $respuesta = array('status' => 'failed','error' => $e->errorInfo[1]);
}
}


    } else {

        $respuesta = array('status' => 'notFound', 'msg' => 'Artículo NO Existe' );
    }
    
     

    header("Content-Type: application/json");
    echo json_encode($respuesta);
});

$app->post('/api/ofab/cerrar', function(Request $request, Response $response, $args){
    
        $id = $request->getParsedBody()['id'];
        $observaciones = $request->getParsedBody()['observaciones'];
        $fecha_fin = $request->getParsedBody()['fecha_fin'];
        
        
        $query = "UPDATE orden_fabricacion SET
                    observaciones = :observaciones,
                    fecha_fin = :fecha_fin  
                    WHERE id = :id LIMIT 1";
                    
    
        try {
            $db = new db();
            $db = $db->connect();
                
            $stmt = $db->prepare($query);
    
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':observaciones', $observaciones);
    
            $stmt->execute();
            $db = null;
    
            if($id > 0) { 
                $respuesta = array('status' => 'ok', 'Orden Cerrada ID' => $id, 'Orden de Fabricacion Cerrada' => $id.' '.$articulo);
            }
    
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $respuesta = array('status' => 'failed', 'msg' => 'Ya existe la orden '.$id );
            } else {
                $respuesta = array('status' => 'failed','error' => $e->errorInfo[1]);
            }
        }
    
        header("Content-Type: application/json");
        echo json_encode($respuesta);
    });
