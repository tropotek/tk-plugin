<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Plg;

/**
 * Hook
 *
 *
 * @package Plg
 */
class Hook extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * @var array
     */
    static public $hookList = array();

    /**
     * @var string
     */
    protected $method = '';

    /**
     * @var string
     */
    protected $args = array();


    /**
     * construct
     *
     * @param string $method
     * @param array $args
     */
    public function __construct($method, $args = array())
    {
        $this->method = $method;
        $this->args = $args;
        $dat = array();
        $dat['method'] = $this->method;
        //$dat['args'] = array_keys($this->args);
        $dat['args'] = array();
        foreach ($args as $k => $v) {
            $str = gettype($v);
            if (is_object($v)) {
                $str = get_class($v);
            }
            $dat['args'][$k] = $str;
        }

        self::$hookList[$this->method] = $dat;
    }

    /**
     * create
     *
     * @param string $method
     * @param array $args
     * @return Hook
     */
    static function create($method, $args = array())
    {
        $obj = new self($method, $args);
        return $obj;
    }

    /**
     * execute
     *
     * @param mixed $obs
     */
    public function update($obs)
    {
        $this->getConfig()->getPluginFactory()->executeHook($this->method, $this->args);
    }

}