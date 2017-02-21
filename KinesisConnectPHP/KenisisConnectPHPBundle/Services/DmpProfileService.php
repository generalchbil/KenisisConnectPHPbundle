<?php

namespace KenisisConnectPHP\KenisisConnectPHPBundle\Services;

use KenisisConnectPHP\KenisisConnectPHPBundle\Model\Profile;
use KenisisConnectPHP\KenisisConnectPHPBundle\Model\Event;
use KenisisConnectPHP\KenisisConnectPHPBundle\Model\Kinesis;
use Aws\Kinesis\KinesisClient;
use Symfony\Bridge\Monolog\Logger;

class DmpProfileService
{
    protected $api_dragibus_url_profile;
    protected $api_dragibus_url_matching;
    protected $api_get_segments;
    protected $kinesis_stream_name;
    protected $awsArgs;
    protected $logger;

    public function __construct($logger,$api_dragibus_url_profile, $api_dragibus_url_matching,$api_get_segments, $kinesis_stream_name, $awsArgs)
    {
        $this->logger                     = $logger;
        $this->api_dragibus_url_profile   = $api_dragibus_url_profile;
        $this->api_dragibus_url_matching  = $api_dragibus_url_matching;
        $this->api_get_segments           = $api_get_segments;
        $this->kinesis_stream_name        = $kinesis_stream_name;
        $this->awsArgs                    = $awsArgs;

    }


    public function createProfile($key=false)
    {

        try{
          return new Profile($key);
        }catch(\Exception $e){
          $this->logger->addError(sprintf('[CREATEPROFILE] error during create Profile with The Key  %s ,  error message : %s',$key, $e->getMessage()));
        }

    }

    public function createEvent($type,$value,$context = false)
    {
      try
      {

        return new Event($type,$value,$context);

      }catch(\Exception $e){

        $this->logger->addError(sprintf('[CREATEEVENT] error during create Event with type  %s ,value : %s,  error message : %s', $type, $value, $e->getMessage()));
      }

    }
    public function pushEvent($profile)
    {
      try
      {

          foreach($profile->getEvents() as $event)
          {

            if(empty($profile->uid) || empty($profile->source) || empty($event->type) || ( empty($event->value) && $event->value!=0 ))
            {
              return 'Some values are require';
            }

            $data = array(
              "user_id"     => $profile->uid,
              "website_id"  => $profile->source,
              "type"        => $event->type,
              "value"       => $event->value,
              "timestamp"   => time()
            );

            if(!empty($event->context))
            {
              $data['context'] = $event->context;
            }
            $record =  array(
                // Data is required
                'Data' => json_encode($data),
                // PartitionKey is required
                'PartitionKey' => $profile->getUid(),
              );
            $records [] = $record;
          }
          //
          $client = $this->connecttoKinessis();

          $result = $client->putRecords(array(
               // Records is required
               'Records' => $records,
               // StreamName is required
               'StreamName' => $this->kinesis_stream_name,
         ));

         return $result;

      }catch( \Exception $e){

        $this->logger->addError(sprintf('[PUSHEVENT] error during send Event to Kinesis, error message : %s', $e->getMessage()));
      }

    }

    public function getProfile($hash)
    {
      try
      {
        if(empty($hash) ){
          return 'The Hash is required';
        }
        $url = $this->api_dragibus_url_profile.'/'.urlencode($hash);
        return $this->callCurl($url);

      }catch( \Exception $e){

        $this->logger->addError(sprintf('[GETPROFILE] error during Get Profile By UID : %s, error message : %s',$hash, $e->getMessage()));
      }


    }

    public function pushIdMatch($provider,$hash,$uid,$source)
    {
      try{

        if(empty($provider) ||  empty($hash) ||  empty($uid)  || empty($source)){
          return 'Some values are require';
        }

        $url = $this->api_dragibus_url_matching.'?dpk='.$provider.'&dpuid='.$hash.'&uid='.urlencode($uid).'&odpk='.$source;
        return $this->callCurl($url);

      }catch(\Exception $e){

        $this->logger->addError(sprintf('[PUSHIDMATCH] error during pushIdMatch : HASH %s, UID %s ,error message : %s',$hash,$uid, $e->getMessage()));
      }


    }

    public function getSegmentsByUser($uid,$created_date=false)
    {
      try{

        if($created_date && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$created_date))
        {
          $this->logger->addError(sprintf('[GETSEGMENTSBYUSER] error during getSegmentsByUser : Error, The format create date should be YYYY-MM-DD'));
          return;
        }
        if(empty($uid)){
          $this->logger->addError(sprintf('[GETSEGMENTSBYUSER] error during getSegmentsByUser : Error, The User id is required'));
          return;
        }

        $url = $this->api_get_segments.'/'.urlencode($uid);

        if($created_date){
          $url.='?created_at='.$created_date;
        }

        return $this->callCurl($url);

      }catch(\Exception $e){

        $this->logger->addError(sprintf('[GETSEGMENTSBYUSER] error during getSegmentsByUser : UID %s, DATE %s ,error message : %s',$uid,$created_date, $e->getMessage()));
      }

    }

    private function callCurl($url){
      //  Initiate curl
      $ch = curl_init();
      // Disable SSL verification
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      // Will return the response, if false it print the response
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Set the url
      curl_setopt($ch, CURLOPT_URL,$url);
      // set timeout
      curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);
      // Execute
      $result=curl_exec($ch);
      // Closing
      curl_close($ch);
      return  json_decode($result);
    }
    protected function connecttoKinessis()
    {
        return KinesisClient::factory($this->awsArgs);
    }

}
