<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface; // ✅ ESTA es la forma correcta
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// Ruta opcional para manejar las OPTIONS (preflight CORS)
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

// Middleware CORS ✅ CORREGIDO
$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin','*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


// Rutas de tu proyecto
(require __DIR__. '/src/routes/usuarioRutas.php')($app);
(require __DIR__. '/src/routes/MazoRutas.php')($app);
(require __DIR__. '/src/routes/juegoRutas.php')($app);
(require __DIR__. '/src/routes/estadisticas.php')($app);
(require __DIR__. '/src/routes/comodin.php')($app);

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});
$app->run();
