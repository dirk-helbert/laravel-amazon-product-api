<?php

namespace Revolution\Amazon\ProductAdvertising;

use Illuminate\Support\Traits\Macroable;

use Revolution\Amazon\ProductAdvertising\Contracts\Factory;

use ApaiIO\ApaiIO;

use ApaiIO\Operations\OperationInterface;

use ApaiIO\Operations\Search;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\BrowseNodeLookup;

class AmazonClient implements Factory
{
    use Macroable;
    use Hookable;

    /**
     * @var ApaiIO
     */
    protected $api;

    /**
     * @var string
     */
    protected $idType = 'ASIN';

    /**
     * constructor.
     *
     * @param  ApaiIO  $api
     *
     */
    public function __construct(ApaiIO $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function config(ApaiIO $api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run(OperationInterface $operation)
    {
        $result = $this->api->runOperation($this->callHook('run', $operation));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $category, string $keyword = null, int $page = 1)
    {
        $search = new Search();

        $search->setCategory($category);
        $search->setKeywords($keyword);
        $search->setResponseGroup(['Large']);
        if ($page > 0) {
            $search->setPage($page);
        }

        $search = $this->callHook('search', $search);

        return $this->run($search);
    }

    /**
     * {@inheritdoc}
     */
    public function browse(string $node, string $response = 'TopSellers')
    {
        $browse = new BrowseNodeLookup();

        $browse->setNodeId($node);
        $browse->setResponseGroup([$response]);

        $browse = $this->callHook('browse', $browse);

        return $this->run($browse);
    }

    /**
     * {@inheritdoc}
     */
    public function item(string $asin)
    {
        $lookup = new Lookup();

        $lookup->setItemId($asin);
        $lookup->setResponseGroup(['Large']);
        $lookup->setIdType($this->getIdType());

        $lookup = $this->callHook('item', $lookup);

        return $this->run($lookup);
    }

    /**
     * {@inheritdoc}
     */
    public function items(array $asin)
    {
        $lookup = new Lookup();

        $lookup->setItemIds($asin);
        $lookup->setResponseGroup(['Large']);
        $lookup->setIdType($this->getIdType());

        $lookup = $this->callHook('items', $lookup);

        return $this->run($lookup);
    }

    /**
     * @inheritDoc
     */
    public function setIdType(string $idType): Factory
    {
        $this->idType = $idType;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIdType(): string
    {
        return $this->idType;
    }
}
