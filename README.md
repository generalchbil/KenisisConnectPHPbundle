KenisisConnectPHP
==========

A Symfony project created on October 14, 2015, 10:24 am.


## Installation && Download:


Add to your composer.json

``` js
{
   [..]
   "repositories": [
      {
         "type": "vcs",
         "url": "https://github.com/generalchbil/KenisisConnectPHPbundle.git"
      }
   ],
   [..]
}
```

Download the bundle:


``` bash

curl -sS https://getcomposer.org/installer | php

php composer.phar require KenisisConnectPHP/KenisisConnectPHPbundle:dev-master

```


## Register

Enable the bundle in the kernel:

``` php
// app/AppKernel.php
// ...
public function registerBundles()
{
    $bundles = array(
        // ...
        new KenisisConnectPHP\KenisisConnectPHPBundle\KenisisConnectPHPBundle(),
    );
}
```


This bundle allows you to specify a **default parameters file** so you only have to specify dynamic parameters in your services.

Add this parameter in your path_project/app/config/parameters.yml.dist

```yml
# Bundle KenisisConnectPHP Config
kenisis_connect:
    api_dragibus_url_profile: Api.Dragibus.url.Profile
    api_dragibus_url_matching: Api.Dragibus.url.Matching
    api_get_segments: Api.Get.Segments
    kinesis_stream_name: AWS.Kinesis.StreamName
    aws:
        key: Aws.Credentials.access_key
        secret: Aws.Credentials.secret_key
        region: Aws.Region
```

## Usage 1 (V0 : trigger events)

### Algorithm
#### Create a new Profile

Un profile est definie par :
    * Uid = l'identifiant unique d'un profile : c'est le md5 d'un email en miniscul md5(strtolower($mail))
    * Source : chaque profile doit avoir une source ('mobile','presta'....)
    * une collection des events :

Un event est definie par :
    * type      (string && require)
    * value     (string && require)
    * context   (boolean && not require)

  ``` php
  <?php

   $service = $this->get('kenisis_connect.service');

   $profile = $service->createProfile($email=false);
   $profile->setSource('mobile');
   $event1 = $service->createEvent('sexe','F');
   $profile->addEvent($event1);

   $event2 = $service->createEvent('tranche_age','23','formulaire');
   $profile->addEvent($event2);
   .
   .
   .
   $event_n = new Event($type_n,$value_n,$context =false);
   $profile->addEvent($event_n);

   $service->pushEvent($profile);

  ?>
  ```


### Client kinesis
``` php
<?php

 use Aws\Kinesis\KinesisClient;

 $client = KinesisClient::factory(array(
             'key'       => '*******',
             'secret'    => '*******',
             'region'    => '*******'
         ));

?>
```
### pushEvent

 After create your profile , you can use this functon to send all events attached to your profile

 ``` php
 <?php

 $service = $this->get('kenisis_connect.service');

 $service->pushEvent($profile);
 ?>
 ```
 We use a custom function putRecords to send events to kinesis


 ``` php
 <?php

 foreach($profile->events as $event){
   $data = array(
     // contient all value require see this doc https://confluence.fullsix.com/pages/viewpage.action?pageId=101727385
     )
   $record =  array(
        // Data is required
        'Data' => json_encode($data),
        // PartitionKey is required
        'PartitionKey' => $profile->getUid(),
    );
    $records [] = $record;
 }

 $result = $client->putRecords(array(
     // Records is required
     'Records' => $records,
     // StreamName is required
     'StreamName' => 'kinesis_stream_name',
 ));

 ?>
 ```

### Get profile
``` php
<?php
$service = $this->get('kenisis_connect.service');

$service->getProfile($email);

 ?>
 ```
### pushIdMatch
``` php
<?php
$service = $this->get('kenisis_connect.service');
/**
* $match_provider : identifiant du fournisseur de données. Exemple: APPNEXUS
* $match_uid     : identifiant de l'utilisateur chez le fournisseur associé
* $profile_uid      : identifiant pivot de l'utilisateur (s'il est fourni, cela surclasse le cookie tiers qui serait reçu)
* $profile_source   : identifiant du fournisseur de données, auquel le paramètre uid est associé
*/
$service->pushIdMatch($match_provider,$match_uid,$profile_uid,$profile_source);

 ?>
```

### getSegmentsByUser
retourne la liste de segments d'un utilisateur
``` php
<?php
$service = $this->get('kenisis_connect.service');
// $profile_uid : identifiant d'utilisateur
// $created_date: contraint date sous format 'YYYY-MM-DD'
$service->getSegmentsByUser($profile_uid,$created_date);

 ?>
```

## Usage 2 (orm or entityManager style)
