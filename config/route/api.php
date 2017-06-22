<?php
/**
 * Routes.
 */


/**
 * Fill the session with some default data.
 *
 * @return void
 */
function initSessionWithDataset($app)
{
    $files = ["users"];
    $dataset = [];
    foreach ($files as $file) {
        $content = file_get_contents(ANAX_INSTALL_PATH . "/api/$file.json");
        $dataset[$file] = json_decode($content, true);
    }

    $app->session->set("api", $dataset);
}



/**
 * Start/create a session and store some default data in it.
 */
$app->router->add("api/**", function () use ($app) {
    $app->session->start();

    $data = $app->session->get("api");
    if (is_null($data)) {
        initSessionWithDataset($app);
    }
});



/**
 * Re-init the session with a default dataset.
 */
$app->router->get("api/init", function () use ($app) {
    initSessionWithDataset($app);
    $app->response->sendJson(["message" => "The session is initiated with the default dataset."]);
    exit;
});



/**
 * Get a subset of a particular dataset
 */
$app->router->get("api/{things:alphanum}", function ($things) use ($app) {
    $data = $app->session->get("api");

    $dataset = array_key_exists($things, $data)
        ? $data[$things]
        : [];

    $offset = $app->request->getGet("offset", 0);
    $limit = $app->request->getGet("limit", 10);
    $res = [
        "data" => array_slice($dataset, $offset, $limit),
        "offset" => $offset,
        "limit" => $limit,
        "total" => count($dataset)
    ];

    $app->response->sendJson($res);
});



/**
 * Get one item from a particular dataset
 */
$app->router->get("api/{things:alphanum}/{id:digit}", function ($things, $id) use ($app) {
    $data = $app->session->get("api");

    $dataset = array_key_exists($things, $data)
        ? $data[$things]
        : [];

    // Find by id
    $found = null;
    foreach ($dataset as $item) {
        if ($item["id"] === $id) {
            $found = $item;
            break;
        }
    }

    if (!$found) {
        $app->response->sendJson(["message" => "The item is not found."]);
        exit;
    }

    $app->response->sendJson($found);
});
