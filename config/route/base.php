<?php
/**
 * Routes.
 */


/**
 * Home
 */
$app->router->add("", function () use ($app) {
    // Default data set sent to layut view
    $data = [
        "title" => "Home",
    ];

    // Get content from markdown file
    $content = file_get_contents(ANAX_INSTALL_PATH . "/content/index.md");
    $content = $app->textfilter->parse($content, ["shortcode", "markdown"]);

    // Add views to a specific region
    $app->view->add("default1/article", [
        "content" => $content->text
    ]);

    // Render a standard page using layout
    $app->renderPage([
        "title" => "Home",
    ]);
});



/**
 * Dump general information
 */
$app->router->add("info", function () use ($app) {
    // Add views to a specific region
    $app->view->add("default1/info");

    // Render a standard page using layout
    $app->renderPage([
        "title" => "Info",
    ]);
});
