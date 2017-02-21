<?php

namespace KenisisConnectPHP\KenisisConnectPHPBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

class Profile
{
    /**
     * @var string
     */
    public $uid;
    /**
     * @var string
     */
    public $source;
    /**
     * @var ArrayCollection
     */
    public $events;

    /**
     * construct
     */
    public function __construct($key=false)
    {

      $this->events = new ArrayCollection();
      if(empty($key) ){
        $this->uid = $this->guid();
      }else
      {
        if(!$this->isValidMd5($key))
        {
          throw new \Exception('your Key is not a valid MD5 ');
        }
        $this->uid = $key;
      }

    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     * @return Profile
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return Profile
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }
    /**
     * @return ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }


    /**
     * @param Event $event
     */
    public function addEvent(Event $event)
    {
        return $this->events->add($event);

    }

    public function isValidMd5($md5 ='')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    private function guid()
    {
      if (function_exists('com_create_guid')){
          return trim(com_create_guid(), '{}');
      }else{
          mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
          $charid = strtoupper(md5(uniqid(rand(), true)));
          $hyphen = chr(45);// "-"
          $uuid = chr(123)
                  .substr($charid, 0, 8).$hyphen
                  .substr($charid, 8, 4).$hyphen
                  .substr($charid,12, 4).$hyphen
                  .substr($charid,16, 4).$hyphen
                  .substr($charid,20,12)
                  .chr(125);
          return trim($uuid,'{}');
      }
    }
}
