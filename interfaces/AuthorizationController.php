<?php namespace Heimdall\Interfaces;

/**
 * Interface AuthorizationController
 * @package Heimdall\Interfaces
 */
interface AuthorizationController
{
    /**
     * AuthorizationController constructor.
     */
    function __construct();

    /**
     * @return mixed
     */
    function authorize();

    /**
     * @return mixed
     */
    function token();
}