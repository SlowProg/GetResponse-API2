## GetResponsePHP

GetResponsePHP is a PHP5 implementation of the [GetResponse API](http://apidocs.getresponse.com/en/api/)

## Requirements

* PHP >= 5.3.0
* [PHP cURL](http://php.net/manual/en/book.curl.php)

## Release Notes

Around 50% of API methods have been implemented with the remainder to follow.

## Examples

```php
<?php

$api = new GetResponseApi2('YOUR_API_KEY');

// Connection Testing
$ping = $api->ping();
var_dump($ping);

// Account
$details = $api->getAccountInfo();
var_dump($details);

// Campaigns
$campaigns 	 = (array)$api->getCampaigns();
$campaignIDs = array_keys($campaigns);
$campaign 	 = $api->getCampaignByID($campaignIDs[0]);
var_dump($campaigns, $campaign);

// Contacts
$contacts 	= (array)$api->getContacts(null);
$contactIDs	= array_keys($contacts);
$setName 	= $api->setContactName($contactIDs[0], 'John Smith');
$setCustoms	= $api->setContactCustoms($contactIDs[0], array('title' => 'Mr', 'middle_name' => 'Fred'));
$customs 	= $api->getContactCustoms($contactIDs[0]);
$contact 	= $api->getContactByID($contactIDs[0]);
$geoIP 		= $api->getContactGeoIP($contactIDs[0]);
$opens 		= $api->getContactOpens($contactIDs[0]);
$clicks 	= $api->getContactClicks($contactIDs[0]);

// Find the contact ID by using email ID and delete the contact
$contactEmail	= (array)$api->getContactsByEmail('EMAIL_ID');
$contactEmailID	= array_keys($contactEmail);
$deleteResponse	= $api->deleteContact($contactEmailID[0]);

var_dump($contacts, $setName, $setCustoms, $customs, $contact, $geoIP, $opens, $clicks);

// Blacklists
$addBlacklist = $api->addAccountBlacklist('someone@domain.co.uk');
$getBlacklist = $api->getAccountBlacklist();
$delBlacklist = $api->deleteAccountBlacklist('someone@domain.co.uk');
var_dump($addBlacklist, $getBlacklist, $delBlacklist);

// or JSON RPC with batch

$api = new GetResponseJsonRpc('YOUR_API_KEY');

$response = $api->batch()
    ->addContact('qwer', 'asdf@asdf.ff', 'asdf')
    ->addContact('asdf', 'qwer@asdf.ff', 'qwer')
    ->send();

var_dump($response);

```
