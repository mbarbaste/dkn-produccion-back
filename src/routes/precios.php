<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/api/precios/{articulo}', function(Request $request, Response $response, $args){
    
        $articulo = $request->getAttribute('articulo');

        if (strlen($articulo) < 4) {
          $articulo = "XXXXXX";
        }

        if(strlen($articulo) == 6) {
            $sql = "SELECT * FROM precios WHERE cod_artic ='".$articulo."'";
        } else {
    
        $sql = "SELECT * FROM `precios` 
                WHERE cod_artic LIKE '".$articulo."%'";
        }
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
    }
); 

    