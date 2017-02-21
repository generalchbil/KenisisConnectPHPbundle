<?php

namespace KenisisConnectPHP\KenisisConnectPHPBundle\Model;

class Event
{
    public $type;
    public $value;
    public $context;


    public function __construct($type,$value,$context=false)
    {
        $this->type     = $type;
        $this->value    = $value;
        if($context) $this->setContext($context);

    }

    public function setContext($context)
    {
        $this->context = $context;
    }

}
