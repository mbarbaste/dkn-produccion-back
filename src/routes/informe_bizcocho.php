<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/informe_bizcocho', function(Request $request, Response $response, $args) {

    $articulo = $request->getParsedBody()['articulo'];
    $desde = $request->getParsedBody()['desde'];
    $hasta = $request->getParsedBody()['hasta'];
    $maquina = $request->getParsedBody()['maquina'];
    $ofab = $request->getParsedBody()['ofab'];
    $tipoPieza = $request->getParsedBody()['tipoPieza'];

    $fechas = "fecha >='".$desde."' AND fecha <='".$hasta."'";

    // if($maquina != 'Todas') {
    //     $maquinas = " AND maquina='".$maquina."'";
    // } else {
    //     $maquinas = "";
    // }

    if($articulo != '') {
        $articulos = " AND articulo LIKE '".$articulo."%'";
    } else {
        $articulos = "";
    }

    if($tipoPieza != 'Todas') {
        $tipoPiezas = " AND grupo='".$tipoPieza."'";
    } else {
        $tipoPiezas = "";
    }

    $query = "SELECT * FROM carga_bizcocho WHERE ".$fechas.$articulos.$tipoPiezas;
    //$query = "SELECT * FROM carga_formacion";

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    
    $cantidadTotal = 0;
    $roturaTotal = 0;
    
    $registros = 0;

    
    $stmt = $db->query($query);

    while($row = $stmt->fetch( PDO::FETCH_ASSOC )) { 
        $cantidadTotal = $cantidadTotal + $row['cantidad'];
        $roturaTotal = $roturaTotal + $row['rotura'];
        $registros++;
   }

    $db = null;

    

    if($registros > 0) {
        
        $porcentajeRotura = $roturaTotal / $cantidadTotal * 100;

        $respuesta = array('cantidad' => $cantidadTotal,
                        'rotura' => $roturaTotal,
                        'porcentajeRotura' => $porcentajeRotura,
                        'registros' => $registros);
    } else {
        $respuesta = array('registros' => 0);
    }
           
    

    header("Content-Type: application/json");
    echo json_encode($respuesta);
 });