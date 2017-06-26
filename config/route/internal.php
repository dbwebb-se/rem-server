<?php
/**
 * Internal routes.
 */
$app->router->addInternal("404", function () use ($app) {
    // Add views to a specific region
    $app->view->add("default1/404", null, "main");

    // Render a standard page using layout
    $app->renderPage([
        "title" => "404",
        "message" => "404",
    ], 404);
});
