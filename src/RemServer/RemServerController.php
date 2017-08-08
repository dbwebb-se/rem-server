<?php

namespace Anax\RemServer;

use \Anax\Common\AppInjectableInterface;
use \Anax\Common\AppInjectableTrait;

/**
 * A controller for the REM Server.
 *
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
class RemServerController implements AppInjectableInterface
{
    use AppInjectableTrait;



    /**
     * Start the session and initiate the REM Server.
     *
     * @return void
     */
    public function anyPrepare()
    {
        $this->app->session->start();

        if (!$this->app->rem->hasDataset()) {
            $this->app->rem->init();
        }
    }



    /**
     * Init or re-init the REM Server.
     *
     * @return void
     */
    public function anyInit()
    {
        $this->app->rem->init();
        $this->app->response->sendJson(["message" => "The session is initiated with the default dataset."]);
        exit;
    }



    /**
     * Destroy the session.
     *
     * @return void
     */
    public function anyDestroy()
    {
        $this->app->session->destroy();
        $this->app->response->sendJson(["message" => "The session was destroyed."]);
        exit;
    }



    /**
     * Get the dataset or parts of it.
     *
     * @param string $key for the dataset
     *
     * @return void
     */
    public function getDataset($key)
    {
        $dataset = $this->app->rem->getDataset($key);
        $offset = $this->app->request->getGet("offset", 0);
        $limit = $this->app->request->getGet("limit", 25);
        $res = [
            "data" => array_slice($dataset, $offset, $limit),
            "offset" => $offset,
            "limit" => $limit,
            "total" => count($dataset)
        ];

        $this->app->response->sendJson($res);
        exit;
    }



    /**
     * Get one item from the dataset.
     *
     * @param string $key    for the dataset
     * @param string $itemId for the item to get
     *
     * @return void
     */
    public function getItem($key, $itemId)
    {
        $item = $this->app->rem->getItem($key, $itemId);
        if (!$item) {
            $this->app->response->sendJson(["message" => "The item is not found."]);
            exit;
        }

        $this->app->response->sendJson($item);
        exit;
    }



    /**
     * Create a new item by getting the entry from the request body and add
     * to the dataset.
     *
     * @param string $key    for the dataset
     *
     * @return void
     */
    public function postItem($key)
    {
        $entry = $this->app->request->getBody();
        $entry = json_decode($entry, true);

        $item = $this->app->rem->addItem($key, $entry);
        $this->app->response->sendJson($item);
        exit;
    }


    /**
     * Upsert/replace an item in the dataset, entry is taken from request body.
     *
     * @param string $key    for the dataset
     * @param string $itemId where to save the entry
     *
     * @return void
     */
    public function putItem($key, $itemId)
    {
        $entry = $this->app->request->getBody();
        $entry = json_decode($entry, true);

        $item = $this->app->rem->upsertItem($key, $itemId, $entry);
        $this->app->response->sendJson($item);
        exit;
    }



    /**
     * Delete an item from the dataset.
     *
     * @param string $key    for the dataset
     * @param string $itemId for the item to delete
     *
     * @return void
     */
    public function deleteItem($key, $itemId)
    {
        $this->app->rem->deleteItem($key, $itemId);
        $this->app->response->sendJson(null);
        exit;
    }



    /**
     * Show a message that the route is unsupported, a local 404.
     *
     * @return void
     */
    public function anyUnsupported()
    {
        $this->app->response->sendJson(["message" => "404. The api/ does not support that."], 404);
        exit;
    }
}
