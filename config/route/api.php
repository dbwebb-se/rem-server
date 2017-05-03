<?php
/**
 * Routes.
 */


/**
 * Create a session and store data in it.
 */
$app->router->get("api/**", function () use ($app) {
    $app->session();

    $data = [
        "offset"    => 0,
        "limit"     => 10,
        "total"     => 36,
        "data"      => [
            "id" => "1",
            "firstName" => "Jannet",
            "lastName" => "Sarro",
        ],
    ];

    $app->response->sendJson($data);
});




/**
 * REM api
 */
$app->router->get("api/{things}", function ($things) use ($app) {
    $data = [
        "offset"    => 0,
        "limit"     => 10,
        "total"     => 36,
        "data"      => [
            "id" => "1",
            "firstName" => "Jannet",
            "lastName" => "Sarro",
        ],
    ];

    $app->response->sendJson($data);
});


/* 
Sheryl Nimmons
Verla Shears
Rod Malick
Onie Thrower
Daren Hunkins
Verlie Apel
Angelyn Varden
Cherry Barna
Dewey Dockery
*/
