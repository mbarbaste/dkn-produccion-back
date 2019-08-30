<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/informe_revisacion', function(Request $request, Response $response, $args) {

    $articulo = $request->getParsedBody()['articulo'];
    $desde = $request->getParsedBody()['desde'];
    $hasta = $request->getParsedBody()['hasta'];
    $horno = $request->getParsedBody()['horno'];
    $ofab = $request->getParsedBody()['ofab'];
    $defecto = $request->getParsedBody()['defecto'];
    $tipoPieza = $request->getParsedBody()['tipoPieza'];

    $fechas = "fecha >='".$desde."' AND fecha <='".$hasta."'";

    if($horno != 'Todos') {
        $hornos = " AND horno='".$horno."'";
    } else {
        $hornos = "";
    }

    if($articulo != '') {
        $articulos = " AND articulo LIKE '".$articulo."%'";
    } else {
        $articulos = "";
    }

    if($defecto != 'Todos') {
        $defectos = " AND ".$defecto." != 0";
    } else {
        $defectos = "";
    }

    if($tipoPieza != 'Todas') {
        $tipoPiezas = " AND grupo LIKE '".$tipoPieza."'";
    } else {
        $tipoPiezas = "";
    }

    $query = "SELECT * FROM carga_revisacion WHERE ".$fechas.$hornos.$articulos.$tipoPiezas;

    $db = new db();
    $db = $db->connect();
        
    $stmt = $db->prepare($query);

    $porcentajeDefecto = 0;
    $cantidadDefecto = 0;
    $cantidadTotal = 0;
    $primera = 0;
    $segunda = 0;
    $quinta = 0;
    $dte = 0;

    $registros = 0;

    
    $stmt = $db->query($query);

    while($row = $stmt->fetch( PDO::FETCH_ASSOC )){ 
        $primera = $primera + $row['primera'];
        $segunda = $segunda + $row['segunda'];
        $quinta = $quinta + $row['quinta'];
        $dte = $dte + $row['dte'];

        $cantidadDefecto = $cantidadDefecto + $row[$defecto];

        $cantidadTotal += $primera + $segunda + $quinta + $dte;

        // if( $row[$defecto] > 0) {
        //     $cantidadDefecto += $row[$defecto];
        // }

        $registros++;
   }

    $db = null;

    

    if($registros > 0) {
        
        $cantidadTotal = $primera + $segunda + $quinta + $dte;

        $porcentajePrimera = $primera / $cantidadTotal * 100;
        $porcentajeSegunda = $segunda / $cantidadTotal * 100;
        $porcentajeQuinta = $quinta / $cantidadTotal * 100;
        $porcentajeDte = $dte / $cantidadTotal * 100;

        $respuesta = array('primera' => $primera,
                        'segunda' => $segunda,
                        'quinta' => $quinta, 
                        'dte' => $dte,
                        'cantidadTotal' => $cantidadTotal,
                        'cantidadDefecto' => $cantidadDefecto,
                        'porcentajePrimera' => $porcentajePrimera,
                        'porcentajeSegunda' => $porcentajeSegunda,
                        'porcentajeQuinta' => $porcentajeQuinta,
                        'porcentajeDte' => $porcentajeDte,
                        'registros' => $registros,
                        'consulta' => $query);
    } else {
        $respuesta = array('registros' => 0);
    }
           
    

    header("Content-Type: application/json");
    echo json_encode($respuesta);
 });