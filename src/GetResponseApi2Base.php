<?php

namespace GetResponse;

/**
 * Abstract GetResponse class
 */
abstract class GetResponseApi2Base
{
	/**
	 * GetResponse API key
	 * https://app.getresponse.com/manage_api.html
	 * @var string
	 */
	protected $apiKey;

	/**
	 * GetResponse API URL
	 * @var string
	 * @access private
	 */
	protected $apiUrl = 'http://api2.getresponse.com';

	/**
	 * Text comparison operators used to filter results
	 * @var array
	 * @access private
	 */
	protected $textOperators = array('EQUALS', 'NOT_EQUALS', 'CONTAINS', 'NOT_CONTAINS', 'MATCHES');

	/**
	 * @param string $apiKey
	 * @param string $apiUrl
	 * @return void
	 */
	public function __construct($apiKey, $apiUrl = null)
	{
		$this->apiKey = $apiKey;

		if ($apiUrl) {
			$this->apiUrl = $apiUrl;
		}
	}

	/**
	 * Test connection to the API, returns "pong" on success
	 * @return string
	 */
	public function ping()
	{
		$response = $this->execute('ping');

		return $response->ping;
	}

	/**
	 * Get basic user account information
	 * @return array
	 */
	public function getAccountInfo()
	{
		$response = $this->execute('get_account_info');

		return $response;
	}

	/**
	 * Get list of email addresses assigned to account
	 * @return array
	 */
	public function getAccountFromFields()
	{
		$response = $this->execute('get_account_from_fields');

		return $response;
	}

	/**
	 * Get single email address assigned to an account using the account From Field ID
	 * @param string $id
	 * @return array
	 */
	public function getAccountFromFieldByID($id)
	{
		$response = $this->execute('get_account_from_field', array('account_from_field' => $id));

		return $response;
	}

	/**
	 * Get a list of active campaigns, optionally filtered
	 * @param string $operator Comparison operator
	 * @param string $comparison Text/expression to compare against
	 * @return array
	 */
	public function getCampaigns($operator = 'CONTAINS', $comparison = '%')
	{
		$params = null;

		if (in_array($operator, $this->textOperators)) {
			$params = array('name' => array($operator => $comparison));
		}

		$response = $this->execute('get_campaigns', $params);

		return $response;
	}

	/**
	 * Return a campaign by ID
	 * @param string $id Campaign ID
	 * @return array
	 */
	public function getCampaignByID($id)
	{
		$response = $this->execute('get_campaign', array('campaign' => $id));

		return $response;
	}

	/**
	 * Return a campaign ID by name
	 * @param string $name Campaign Name
	 * @return string Campaign ID
	 */
	public function getCampaignByName($name)
	{
		$response = $this->execute('get_campaigns', array('name' => array('EQUALS' => $name)));

		return key($response);
	}

	/**
	 * Return a list of messages, optionally filtered by multiple conditions
	 * @todo Implement all conditions, this is unfinished
	 * @param array|null $campaigns Optional argument to narrow results by campaign ID
	 * @param string|null $type Optional argument to narrow results by "newsletter", "autoresponder", or "draft"
	 * @param string $operator
	 * @param string $comparison
	 * @return array
	 */
	public function getMessages($campaigns = null, $type = null, $operator = 'CONTAINS', $comparison = '%')
	{
		$params = null;

		if (is_array($campaigns)) {
			$params['campaigns'] = $campaigns;
		}

		if (is_string($type)) {
			$params['type'] = $type;
		}

		$response = $this->execute('get_messages', $params);

		return $response;
	}

	/**
	 * Return a message by ID
	 * @param string $id Message ID
	 * @return array
	 */
	public function getMessageByID($id)
	{
		$response = $this->execute('get_message', array('message' => $id));

		return $response;
	}

	/**
	 * Return message contents by ID
	 * @param string $id Message ID
	 * @return array
	 */
	public function getMessageContents($id)
	{
		$response = $this->execute('get_message_contents', array('message' => $id));

		return $response;
	}

	/**
	 * Return message statistics
	 * @param string $message Message ID
	 * @param string $grouping grouping
	 * @return array|null
	 */
	public function getMessageStats($message, $grouping = "yearly")
	{
		$params = [
			'message' => $message,
			'grouping' => $grouping,
		];

		$response = $this->execute('get_message_stats', $params);

		return $response;
	}

	/**
	 * Add autoresponder to a campaign at a specific day of cycle
	 * @param string $campaign Campaign ID
	 * @param string $subject Subject of message
	 * @param array $contents Allowed keys are "plain" and "html", at least one is mandatory
	 * @param int $cycle_day
	 * @param string $from_field From Field ID obtained through getAccountFromFields()
	 * @param array $flags Enables extra functionality for a message: "clicktrack", "subscription_reminder", "openrate", "google_analytics"
	 * @return array
	 */
	public function addAutoresponder($campaign, $subject, $cycle_day, $html = null, $plain = null, $from_field = null, $flags = null)
	{
		$params = array('campaign' => $campaign, 'subject' => $subject, 'day_of_cycle' => $cycle_day);

		if (is_string($html)) {
			$params['contents']['html'] = $html;
		}

		if (is_string($plain)) {
			$params['contents']['plain'] = $plain;
		}

		if (is_string($from_field)) {
			$params['from_field'] = $from_field;
		}

		if (is_array($flags)) {
			$params['flags'] = $flags;
		}

		$response = $this->execute('add_autoresponder', $params);

		return $response;
	}

	/**
	 * Delete an autoresponder
	 * @param string $id
	 * @return array
	 */
	public function deleteAutoresponder($id)
	{
		$response = $this->execute('delete_autoresponder', array('message' => $id));

		return $response;
	}

	/**
	 * Return a list of contacts, optionally filtered by multiple conditions
	 * @todo Implement all conditions, this is unfinished
	 * @param array|null $campaigns Optional argument to narrow results by campaign ID
	 * @param string $operator Optional argument to change operator (default is 'CONTAINS')
	 *		See https://github.com/GetResponse/DevZone/tree/master/API#operators for additional operator options
	 * @param string $comparison
	 * @param array $fields (an associative array, the keys of which should enable/disable comparing name or email)
	 * @return array
	 */
	public function getContacts($campaigns = null, $operator = 'CONTAINS', $comparison = '%', $fields = array('name' => true, 'email' => false))
	{
		$params = null;

		if (is_array($campaigns)) {
			$params['campaigns'] = $campaigns;
		}

		if ($fields['name']) {
			$params['name'] = $this->prepTextOp($operator, $comparison);
		}

		if ($fields['email']) {
			$params['email'] = $this->prepTextOp($operator, $comparison);
		}

		$response = $this->execute('get_contacts', $params);

		return $response;
	}

	/**
	 * Return a list of contacts by email address (optionally narrowed by campaign)
	 * @param string $email Email Address of Contact (or a string contained in the email address)
	 * @param array|null $campaigns Optional argument to narrow results by campaign ID
	 * @param string $operator Optional argument to change operator (default is 'CONTAINS')
	 *		See https://github.com/GetResponse/DevZone/tree/master/API#operators for additional operator options
	 * @return array
	 */
	public function getContactsByEmail($email, $campaigns = null, $operator = 'CONTAINS')
	{
		$params = null;
		$params['email'] = $this->prepTextOp($operator, $email);

		if (is_array($campaigns)) {
			$params['campaigns'] = $campaigns;
		}

		$response = $this->execute('get_contacts', $params);

		return $response;
	}

	/**
	 * Return a list of contacts filtered by custom contact information
	 * $customs is an associative arrays, the keys of which should correspond to the
	 * custom field names of the customers you wish to retrieve.
	 * @param array|null $campaigns Optional argument to narrow results by campaign ID
	 * @param string $operator
	 * @param array $customs
	 * @param string $comparison
	 * @return array
	 */
	public function getContactsByCustoms($campaigns = null, $customs, $operator = 'EQUALS')
	{
		$params = null;

		if (is_array($campaigns)) {
			$params['campaigns'] = $campaigns;
		}

		if (!is_array($customs)) {
			throw new GetResponseApi2Exception('Second argument must be an array');
		}

		foreach ($customs as $key => $val) {
			$params['customs'][] = array(
				'name' => $key,
				'content' => $this->prepTextOp($operator, $val)
			);
		}

		$response = $this->execute('get_contacts', $params);

		return $response;
	}

	/**
	 * Return a contact by ID
	 * @param string $id User ID
	 * @return array
	 */
	public function getContactByID($id)
	{
		$response = $this->execute('get_contact', array('contact' => $id));

		return $response;
	}


	/**
	 * Set a contact name
	 * @param string $id User ID
	 * @return array
	 */
	public function setContactName($id, $name)
	{
		$response = $this->execute('set_contact_name', array('contact' => $id, 'name' => $name));

		return $response;
	}

	/**
	 * Set a contact cycle
	 * @param string $id User ID
	 * @param int $cycle_day Cycle Day
	 * @return array
	 */
	public function setContactCycle($id, $cycle_day)
	{
		$response = $this->execute('set_contact_cycle', array('contact' => $id, 'cycle_day' => $cycle_day));

		return $response;
	}

	/**
	 * Set a contact campaign
	 * @param string $id User ID
	 * @param string $campaign Campaign ID
	 * @return array
	 */
	public function setContactCampaign($id, $campaign)
	{
		$response = $this->execute('move_contact', array('contact' => $id, 'campaign' => $campaign));

		return $response;
	}

	/**
	 * Return a contacts custom information
	 * @param string $id User ID
	 * @return array
	 */
	public function getContactCustoms($id)
	{
		$response = $this->execute('get_contact_customs', array('contact' => $id));

		return $response;
	}


	/**
	 * Set custom contact information
	 * $customs is an associative array, the keys of which should correspond to the
	 * custom field name you wish to add/modify/remove.
	 * Actions: added if not present, updated if present, removed if value is null
	 * @todo Implement multivalue customs.
	 * @param string $id User ID
	 * @param array $customs
	 * @return array
	 */
	public function setContactCustoms($id, $customs)
	{
		if (!is_array($customs)) {
			throw new GetResponseApi2Exception('Second argument must be an array');
		}

		foreach ($customs as $key => $val) {
			$params[] = array('name' => $key, 'content' => $val);
		}

		$response = $this->execute('set_contact_customs', array('contact' => $id, 'customs' => $params));

		return $response;
	}

	/**
	 * Return a contacts GeoIP
	 * @param string $id User ID
	 * @return array
	 */
	public function getContactGeoIP($id)
	{
		$response = $this->execute('get_contact_geoip', array('contact' => $id));

		return $response;
	}

	/**
	 * List dates when the messages were opened by contacts
	 * @param string $id User ID
	 * @return array
	 */
	public function getContactOpens($id)
	{
		$response = $this->execute('get_contact_opens', array('contact' => $id));

		return $response;
	}

	/**
	 * List dates when the links in messages were clicked by contacts
	 * @param string $id User ID
	 * @return array
	 */
	public function getContactClicks($id)
	{
		$response = $this->execute('get_contact_clicks', array('contact' => $id));

		return $response;
	}

	/**
	 * Add contact to the specified list (Requires email verification by contact)
	 * The return value of this function will be "queued", and on subsequent
	 * submission of the same email address will be "duplicated".
	 * @param string $campaign Campaign ID
	 * @param string $email Email address of contact
	 * @param string $name Name of contact
	 * @param string $action Standard, insert or update
	 * @param int $cycle_day
	 * @param array $customs
	 * @param string $ipAddress
	 * @return array
	 */
	public function addContact($campaign, $email, $name = '', $action = 'standard', $cycle_day = 0, $customs = array(), $ipAddress = null)
	{
		$params = array('campaign' => $campaign, 'action' => $action, 'email' => $email, 'cycle_day' => $cycle_day);

		if ($name) {
			$params['name'] = $name;
		}

		if ($ipAddress) {
			$params['ipAddress'] = $ipAddress;
		}

		if (!empty($customs)) {
			foreach($customs as $key => $val) $c[] = array('name' => $key, 'content' => $val);
			$params['customs'] = $c;
		}

		$response = $this->execute('add_contact', $params);

		return $response;
	}

	/**
	 * Delete a contact
	 * @param string $id
	 * @return array
	 */
	public function deleteContact($id)
	{
		$response = $this->execute('delete_contact', array('contact' => $id));

		return $response;
	}

	/**
	 * Get blacklist masks on account level
	 * Account is determined by API key
	 * @return array
	 */
	public function getAccountBlacklist()
	{
		$response = $this->execute('get_account_blacklist');

		return $response;
	}

	/**
	 * Adds blacklist mask on account level
	 * @param string $mask
	 * @return array
	 */
	public function addAccountBlacklist($mask)
	{
		$response = $this->execute('add_account_blacklist', array('mask' => $mask));

		return $response;
	}

	/**
	 * Delete blacklist mask on account level
	 * @param string $mask
	 * @return array
	 */
	public function deleteAccountBlacklist($mask)
	{
		$response = $this->execute('delete_account_blacklist', array('mask' => $mask));

		return $response;
	}

	/**
	 * Get Webforms
	 * @return array
	 */
	public function getWebForms()
	{
		$response = $this->execute('get_webforms');

		return $response;
	}

	/**
	 * Get Webforms
	 * @param int $id Webform ID
	 * @return array
	 */
	public function getWebForm($id)
	{
		$response = $this->execute('get_webform', array('webform' => $id));

		return $response;
	}

	/**
	 * Return a key => value array for text comparison
	 * @param string $operator
	 * @param mixed $comparison
	 * @return array
	 * @access private
	 */
	protected function prepTextOp($operator, $comparison)
	{
		if (!in_array($operator, $this->textOperators)) {
			throw new GetResponseApi2Exception('Invalid text operator');
		}

		if ($operator === 'CONTAINS') {
			$comparison = '%'.$comparison.'%';
		}

		return array($operator => $comparison);
	}

	/**
	 * Executes an API call
	 * @param string $method API method to call
	 * @param array $params Array of parameters
	 * @return array
	 * @access private
	 */
	abstract protected function execute($method, array $params = []);
}
