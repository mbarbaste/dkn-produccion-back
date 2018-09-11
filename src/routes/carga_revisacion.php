<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function descargaHornoAlta($oid, $primera, $segunda, $quinta, $descarte, $articulo) {

    $status = "proceso";

    $cantidad_total = $primera + $segunda + $quinta + $descarte;

    // Actualizo ofab + revisacion / - horno_alta
    $query = "UPDATE orden_fabricacion SET 
                revisacion = revisacion + ".$cantidad_total.", 
                horno_alta = horno_alta - ".$cantidad_total."  
                WHERE id = ".$oid." LIMIT 1";

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    if($stmt->execute()) {
        $status = "ok";
    } else {
        $status = "falla";
    }

    $db = null;

    // Actualizo stock revisacion en articulos
    $query = "UPDATE articulos SET 
                stock_horno_alta = stock_horno_alta - ".$cantidad_total.", 
                stock_revisacion_1 = stock_revisacion_1 + ".$primera.", 
                stock_revisacion_2 = stock_revisacion_2 + ".$segunda.", 
                stock_revisacion_5 = stock_revisacion_5 + ".$quinta.", 
                stock_revisacion_dte = stock_revisacion_dte + ".$descarte."
                 WHERE articulo = '".$articulo."' LIMIT 1";                
    
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);
    
        if($stmt->execute()) {
            $status = "ok";
        } else {
            $status = "falla";
        }

    $db = null;

    return $status;
}

$app->get('/api/revisacion/{ofab}', function(Request $request, Response $response, $args){
    
    $ofab = $request->getAttribute('ofab');

    $sql = "SELECT * FROM `carga_revisacion` 
            WHERE ofab_id = '".$ofab."'";

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


$app->post('/api/carga_revisacion', function(Request $request, Response $response, $args) {

    $ofab_id = $request->getParsedBody()['ofab_id'];
    $articulo = $request->getParsedBody()['articulo'];
    $fecha = $request->getParsedBody()['fecha'];
    $primera = $request->getParsedBody()['primera'];
    $segunda = $request->getParsedBody()['segunda'];
    $quinta = $request->getParsedBody()['quinta'];
    $descarte = $request->getParsedBody()['descarte'];

    $d01 = $request->getParsedBody()['d01'];
    $d02 = $request->getParsedBody()['d02'];
    $d03 = $request->getParsedBody()['d03'];
    $d04 = $request->getParsedBody()['d04'];
    $d05 = $request->getParsedBody()['d05'];
    $d06 = $request->getParsedBody()['d06'];
    $d07 = $request->getParsedBody()['d07'];
    $d08 = $request->getParsedBody()['d08'];
    $d09 = $request->getParsedBody()['d09'];
    $d10 = $request->getParsedBody()['d10'];

    $d11 = $request->getParsedBody()['d11'];
    $d12 = $request->getParsedBody()['d12'];
    $d13 = $request->getParsedBody()['d13'];
    $d14 = $request->getParsedBody()['d14'];
    $d15 = $request->getParsedBody()['d15'];
    $d16 = $request->getParsedBody()['d16'];
    $d17 = $request->getParsedBody()['d17'];
    $d18 = $request->getParsedBody()['d18'];
    $d19 = $request->getParsedBody()['d19'];
    $d20 = $request->getParsedBody()['d20'];

    $d21 = $request->getParsedBody()['d21'];
    $d22 = $request->getParsedBody()['d22'];
    $d23 = $request->getParsedBody()['d23'];
    $d24 = $request->getParsedBody()['d24'];


    $query = "INSERT INTO carga_revisacion (  
                ofab_id,
                fecha,
                articulo, 
                primera,
                segunda,
                quinta,
                dte,
                d01,
                d02,
                d03,
                d04,
                d05,
                d06,
                d07,
                d08,
                d09,
                d10,
                d11,
                d12,
                d13,
                d14,
                d15,
                d16,
                d17,
                d18,
                d19,
                d20,
                d21,
                d22,
                d23,
                d24
                ) VALUES (
                    :ofab_id, 
                    :fecha,
                    :articulo,
                    :1era,
                    :2da,
                    :5ta,
                    :dte,
                    :d01,
                    :d02,
                    :d03,
                    :d04,
                    :d05,
                    :d06,
                    :d07,
                    :d08,
                    :d09,
                    :d10,
                    :d11,
                    :d12,
                    :d13,
                    :d14,
                    :d15,
                    :d16,
                    :d17,
                    :d18,
                    :d19,
                    :d20,
                    :d21,
                    :d22,
                    :d23,
                    :d24
                    )";   

    try {
        $db = new db();
        $db = $db->connect();
            
        $stmt = $db->prepare($query);

        $stmt->bindParam(':ofab_id', $ofab_id);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':articulo', $articulo);

        $stmt->bindParam(':1era', $primera);
        $stmt->bindParam(':2da', $segunda);
        $stmt->bindParam(':5ta', $quinta);
        $stmt->bindParam(':dte', $descarte);

        $stmt->bindParam(':d01', $d01);
        $stmt->bindParam(':d02', $d02);
        $stmt->bindParam(':d03', $d03);
        $stmt->bindParam(':d04', $d04);
        $stmt->bindParam(':d05', $d05);
        $stmt->bindParam(':d06', $d06);
        $stmt->bindParam(':d07', $d07);
        $stmt->bindParam(':d08', $d08);
        $stmt->bindParam(':d09', $d09);
        $stmt->bindParam(':d10', $d10);

        $stmt->bindParam(':d11', $d11);
        $stmt->bindParam(':d12', $d12);
        $stmt->bindParam(':d13', $d13);
        $stmt->bindParam(':d14', $d14);
        $stmt->bindParam(':d15', $d15);
        $stmt->bindParam(':d16', $d16);
        $stmt->bindParam(':d17', $d17);
        $stmt->bindParam(':d18', $d18);
        $stmt->bindParam(':d19', $d19);
        $stmt->bindParam(':d20', $d20);

        $stmt->bindParam(':d21', $d21);
        $stmt->bindParam(':d22', $d22);
        $stmt->bindParam(':d23', $d23);
        $stmt->bindParam(':d24', $d24);
        

        $stmt->execute();

        $id = $db->lastInsertId();
        $db = null;

        if($id > 0) { 

            if(descargaHornoAlta($ofab_id, $primera, $segunda, $quinta, $descarte, $articulo) == 'ok') {
                $cantidad_total = $primera + $segunda + $quinta + $descarte;
                $respuesta = array('status' => 'ok', 'msg' => 'Se registró el Parte de Revisación y se actualizaron Órdenes y Stocks','Cantidad_Total' => $cantidad_total);
            } else {
                $respuesta = array('status' => 'fail', 'msg' => 'Se produjo un error al registrar el Parte de Revisación y se actualizaron Órdenes y Stocks');
            }

            } else {
                $respuesta = array('status' => 'error', 'msg' => 'Algo anda mal');
        }

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $respuesta = array('status' => 'failed', 'msg' => 'Ya existe la orden '.$id );
        } else {
            $respuesta = array('status' => 'failed','error' => $e->errorInfo);
        }
    }

    header("Content-Type: application/json");
    echo json_encode($respuesta);
 });