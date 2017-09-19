<?php

namespace Productstore;

/**
 * @SWG\Definition(definition="Product", type="object", required={"reqTime"})
 */
class Productstore
{
     /**
     * @SWG\Property()
     * @var string
     */
    public $reqTime;

    /**
     * @SWG\Property()
     * @var string
     */
    public $channelId;

    /**
     * @var test
     * @SWG\Property()
     */
    public $tag;
}

