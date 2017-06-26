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
 * Destroy the session.
 */
$app->router->get("api/destroy", function () use ($app) {
    $app->session->destroy();
    $app->response->sendJson(["message" => "The session was destroyed."]);
    exit;
});



/**
 * Get a subset of a particular dataset
 */
$app->router->get("api/{dataset:alphanum}", function ($dataset) use ($app) {
    // Get/create the dataset selected
    $data = $app->session->get("api");
    $set = isset($data[$dataset])
        ? $data[$dataset]
        : [];

    $offset = $app->request->getGet("offset", 0);
    $limit = $app->request->getGet("limit", 25);
    $res = [
        "data" => array_slice($set, $offset, $limit),
        "offset" => $offset,
        "limit" => $limit,
        "total" => count($set)
    ];

    $app->response->sendJson($res);
});



/**
 * Get one item from a particular dataset
 */
$app->router->get("api/{dataset:alphanum}/{id:digit}", function ($dataset, $id) use ($app) {
    // Get/create the dataset selected
    $data = $app->session->get("api");
    $set = isset($data[$dataset])
        ? $data[$dataset]
        : [];
    $id = (int) $id;

    // Find by id
    $found = null;
    foreach ($set as $item) {
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



/**
 * Create a new item and add to the dataset
 */
$app->router->post("api/{dataset:alphanum}", function ($dataset) use ($app) {
    // Get/create the dataset selected
    $data = $app->session->get("api");
    $set = isset($data[$dataset])
        ? $data[$dataset]
        : [];

    // Get max value for the id
    $max = 0;
    foreach ($set as $val) {
        if ($max < $val["id"]) {
            $max = $val["id"];
        }
    }

    // Get the entry data from the request body and set the id
    $entry = $app->request->getBody();
    $entry = json_decode($entry, true);
    $entry["id"] = $max + 1;

    // Add new entry to the dataset
    $set[] = $entry;
    $data[$dataset] = $set;
    $app->session->set("api", $data);

    $app->response->sendJson($entry);
});



/**
 * Upsert/replace an item in the dataset
 */
$app->router->put("api/{dataset:alphanum}/{id:digit}", function ($dataset, $id) use ($app) {
    // Get/create the dataset selected
    $data = $app->session->get("api");
    $set = isset($data[$dataset])
        ? $data[$dataset]
        : [];
    $id = (int) $id;

    // Get the entry data from the request body
    $entry = $app->request->getBody();
    $entry = json_decode($entry, true);
    $entry["id"] = $id;

    // Upsert/replace the item in the dataset
    $found = false;
    foreach ($set as $key => $val) {
        if ($id === $val["id"]) {
            $set[$key] = $entry;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $set[] = $entry;
    }

    // Save the updated dataset
    $data[$dataset] = $set;
    $app->session->set("api", $data);

    $app->response->sendJson($entry);
});



/**
 * Delete an item from the dataset
 */
$app->router->delete("api/{dataset:alphanum}/{id:digit}", function ($dataset, $id) use ($app) {
    // Get/create the dataset selected
    $data = $app->session->get("api");
    $set = isset($data[$dataset])
        ? $data[$dataset]
        : [];
    $id = (int) $id;

    // Delete the item in the dataset
    $entry = null;
    foreach ($set as $key => $val) {
        if ($id === $val["id"]) {
            unset($set[$key]);
            break;
        }
    }

    // Save the updated dataset
    $data[$dataset] = $set;
    $app->session->set("api", $data);

    $app->response->sendJson(null);
});
