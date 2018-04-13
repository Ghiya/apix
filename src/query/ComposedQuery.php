<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\query;


class ComposedQuery extends Query
{


    /**
     * @var string
     */
    public $joinIndex = [];


    /**
     * @var array
     */
    public $join = [];


}