<?php
/**
 * Routes for the REM Server.
 */
return [
    "routes" => [
        [
            "info" => "Start the session and initiate the REM Server.",
            "requestMethod" => null,
            "path" => "**",
            "callable" => ["remController", "anyPrepare"]
        ],
        [
            "info" => "Init or re-init the REM Server.",
            "requestMethod" => "get",
            "path" => "init",
            "callable" => ["remController", "anyInit"]
        ],
        [
            "info" => "Destroy the session.",
            "requestMethod" => "get",
            "path" => "destroy",
            "callable" => ["remController", "anyDestroy"]
        ],
        [
            "info" => "Get the dataset or parts of it.",
            "requestMethod" => "get",
            "path" => "{dataset:alphanum}",
            "callable" => ["remController", "getDataset"]
        ],
        [
            "info" => "Get one item from the dataset.",
            "requestMethod" => "get",
            "path" => "{dataset:alphanum}/{id:digit}",
            "callable" => ["remController", "getItem"]
        ],
        [
            "info" => "Create a new item and add to the dataset.",
            "requestMethod" => "post",
            "path" => "{dataset:alphanum}",
            "callable" => ["remController", "postItem"]
        ],
        [
            "info" => "Upsert/replace an item in the dataset.",
            "requestMethod" => "put",
            "path" => "{dataset:alphanum}/{id:digit}",
            "callable" => ["remController", "putItem"]
        ],
        [
            "info" => "Delete an item from the dataset.",
            "requestMethod" => "delete",
            "path" => "{dataset:alphanum}/{id:digit}",
            "callable" => ["remController", "deleteItem"]
        ],
        [
            "info" => "Show a message that the route is unsupported, a local 404.",
            "requestMethod" => null,
            "path" => "**",
            "callable" => ["remController", "anyUnsupported"]
        ],
    ]
];

// /** Start the session and initiate the REM Server. */
// $app->router->add("api/**", [$app->remController, "anyPrepare"]);
// 
// /** Init or re-init the REM Server. */
// $app->router->get("api/init", [$app->remController, "anyInit"]);
// 
// /** Destroy the session. */
// $app->router->get("api/destroy", [$app->remController, "anyDestroy"]);
// 
// /** Get the dataset or parts of it. */
// $app->router->get("api/{dataset:alphanum}", [$app->remController, "getDataset"]);
// 
// /** Get one item from the dataset. */
// $app->router->get("api/{dataset:alphanum}/{id:digit}", [$app->remController, "getItem"]);
// 
// /** Create a new item and add to the dataset */
// $app->router->post("api/{dataset:alphanum}", [$app->remController, "postItem"]);
// 
// /** Upsert/replace an item in the dataset. */
// $app->router->put("api/{dataset:alphanum}/{id:digit}", [$app->remController, "putItem"]);
// 
// /** Delete an item from the dataset */
// $app->router->delete("api/{dataset:alphanum}/{id:digit}", [$app->remController, "deleteItem"]);
// 
// /** Show a message that the route is unsupported, a local 404. */
// $app->router->add("api/**", [$app->remController, "anyUnsupported"]);
