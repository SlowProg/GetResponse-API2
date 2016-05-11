<?php

namespace GetResponse;

/**
 * GetResponse Class
 */
class GetResponseApi2 extends GetResponseApi2Base
{
	/**
	 * Get single email address assigned to an account using an email address
	 * @param string $email
	 * @return array
	 */
	public function getAccountFromFieldsByEmail($email)
	{
		$response = $this->getAccountFromFields();

		foreach ($response as $key => $account) {
			if ($account->email != $email) {
				unset($response->$key);
			}
		}

		return $response;
	}

	/**
	 * Return an autoresponder message from a campaign by Cycle Day
	 * @param string $campaign Campaign ID
	 * @param string $cycle_day Cycle Day
	 * @return array
	 */
	public function getMessageByCycleDay($campaign, $cycle_day)
	{
		$params['campaigns'] = array($campaign);
		$params['type'] = "autoresponder";

		$response = $this->execute('get_messages', $params);

		foreach ($response as $key => $message) {
			if ($message->day_of_cycle != $cycle_day) {
				unset($response->$key);
			}
		}

		return $response;
	}

	/**
	 * Return autoresponder message contents by Cycle Day
	 * @param string $campaign Campaign ID
	 * @param string $cycle_day Cycle Day
	 * @return array|null
	 */
	public function getMessageContentsByCycleDay($campaign, $cycle_day)
	{
		$params['campaigns'] = array($campaign);
		$params['type'] = "autoresponder";

		$response = $this->execute('get_messages', $params);

		foreach ($response as $key => $message) {
			if ($message->day_of_cycle == $cycle_day) {
				return $this->getMessageContents($key);
			}
		}

		return null;
	}

	/**
	 * Get Webforms
	 * @return array
	 */
	public function getWebForms()
	{
		$response = parent::getWebForms();
		$webForms = array();

		// Loop through the webforms, and get the campaign data for this form
		foreach ($response as $webFormId => $form) {

			$formArray = array();
			$formArray['webform_id'] = $webFormId;
			$formArray['data'] = $form;

			$campaignId = $form->campaign;

			$campaignResponse = $this->getCampaignByID($campaignId);

			$formArray['campaign'] = array(
				'campaign_id' => $campaignId,
				'data' => $campaignResponse->$campaignId
			);
			array_push($webForms, $formArray);
		}

		return $webForms;
	}

	/**
	 * Get Webforms
	 * @param int $id Webform ID
	 * @return array
	 */
	public function getWebForm($id)
	{
		$response = parent::getWebForm($id);
		$webForms = array();

		// Loop through the webforms, and get the campaign data for this form
		foreach ($response as $webFormId => $form) {

			$formArray = array();
			$formArray['webform_id'] = $webFormId;
			$formArray['data'] = $form;

			$campaignId = $form->campaign;

			$campaignResponse = $this->getCampaignByID($campaignId);

			$formArray['campaign'] = array(
				'campaign_id' => $campaignId,
				'data' => $campaignResponse->$campaignId
			);
			array_push($webForms, $formArray);
		}

		if (!empty($webForms)) {
			return $webForms[0];
		} else {
			return $webForms;
		}
	}

	/**
	 * Executes an API call
	 * @param string $method API method to call
	 * @param array $params Array of parameters
	 * @return array
	 * @access private
	 */
	protected function execute($method, array $params = [])
	{
		$array = array($this->apiKey);

		if (!empty($params)) {
			$array[1] = $params;
		}

		$request = json_encode(array(
			'method' => $method,
			'params' => $array,
		));

		$handle = curl_init($this->apiUrl);

		curl_setopt($handle, CURLOPT_POST, 1);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $request);
		curl_setopt($handle, CURLOPT_HEADER, 'Content-type: application/json');
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

		$response = json_decode(curl_exec($handle), true);

		if (curl_error($handle)) {
			throw new GetResponseApi2Exception(curl_error($handle));
		}

		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

		if (!(($httpCode == '200') || ($httpCode == '204'))) {
			throw new GetResponseApi2Exception('API call failed. Server returned status code '.$httpCode.'.');
		}

		curl_close($handle);

		if (!$response['error']) {
			return $response['result'];
		} else {
			throw new GetResponseApi2Exception($response['error']['message']);
		}
	}
}
