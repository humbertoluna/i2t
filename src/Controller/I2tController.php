<?php

namespace Drupal\i2t\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class I2tController enable or disable i2t devices with web requests.
 *
 * @package Drupal\i2t\I2tController
 */
class I2tController extends ControllerBase {

  /**
   * Drupal http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  // Host and URL should be provided by https://i2t.com.mx/
  const HOST = 'http://142.93.72.169/';
  // Devices.
  const FENCE = 'fence';
  const ALARM = 'alarm';
  // Operations.
  const ENABLE = 'ENABLE';
  const DISABLE = 'DISABLE';

  /**
   * I2t Controller constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    ClientInterface $http_client,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory
  ) {
    $this->httpClient = $http_client;
    $this->loggerFactory = $logger_factory->get('i2t Smart');
    $this->i2tConfig = $config_factory->get('i2t.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('logger.factory'),
      $container->get('config.factory')
    );
  }

  /**
   * Disable the Fence device.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The status of the web request to enable the device.
   */
  public function disableFence() {
    $api_key = $this->getApiKey();
    if (!$api_key) {
      $operation_result = $this->t('You need to set the API KEY');
      return new Response($operation_result, 200, []);
    }
    $operation_result = $this->getDeviceStatus(self::FENCE);
    if ($operation_result == 'on') {
      $operation_result = $this->sendToggleRequest(self::FENCE, self::DISABLE);
    }
    elseif ($operation_result == 'off') {
      $operation_result = $this->t('Fence is already off');
    }
    $this->loggerFactory->notice($operation_result);

    return new Response($operation_result, 200, []);
  }

  /**
   * Disable the Alarm device.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The status of the web request to enable the device.
   */
  public function disableAlarm() {
    $api_key = $this->getApiKey();
    if (!$api_key) {
      $operation_result = $this->t('You need to set the API KEY');
      return new Response($operation_result, 200, []);
    }
    $operation_result = $this->getDeviceStatus(self::ALARM);
    if ($operation_result == 'on') {
      $operation_result = $this->sendToggleRequest(self::ALARM, self::DISABLE);
    }
    elseif ($operation_result == 'off') {
      $operation_result = $this->t('Alarm is already off');
    }
    $this->loggerFactory->notice($operation_result);

    return new Response($operation_result, 200, []);
  }

  /**
   * Enable the Fence device.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The status of the web request to enable the device.
   */
  public function enableFence() {
    $api_key = $this->getApiKey();
    if (!$api_key) {
      $operation_result = $this->t('You need to set the API KEY');
      return new Response($operation_result, 200, []);
    }
    $operation_result = $this->getDeviceStatus(self::FENCE);
    if ($operation_result == 'off') {
      $operation_result = $this->sendToggleRequest(self::FENCE, self::ENABLE);
    }
    elseif ($operation_result == 'on') {
      $operation_result = $this->t('Fence is already on');
    }
    $this->loggerFactory->notice($operation_result);

    return new Response($operation_result, 200, []);
  }

  /**
   * Enable the Alarm device.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The status of the web request to enable the device.
   */
  public function enableAlarm() {
    $api_key = $this->getApiKey();
    if (!$api_key) {
      $operation_result = $this->t('You need to set the API KEY');
      return new Response($operation_result, 200, []);
    }
    $operation_result = $this->getDeviceStatus(self::ALARM);
    if ($operation_result == 'off') {
      $operation_result = $this->sendToggleRequest(self::ALARM, self::ENABLE);
    }
    elseif ($operation_result == 'on') {
      $operation_result = $this->t('Alarm is already on');
    }
    $this->loggerFactory->notice($operation_result);

    return new Response($operation_result, 200, []);
  }

  /**
   * Gets the device status.
   *
   * @param string $device
   *   The device in question "fence" or "alarm".
   *
   * @return string
   *   The device status.
   */
  private function getDeviceStatus(string $device) {
    // Do the actual API call to i2t.
    $result = $this->t('Cannot get @device status, make sure to add the x-api-key header on your web request', ['@device' => $device]);
    $method = 'GET';
    $options = [];
    $api_key = $this->getApiKey();
    if ($device == self::FENCE && isset($api_key)) {
      $url = self::HOST . $api_key . '/get/V31';
    }
    elseif ($device == self::ALARM && isset($api_key)) {
      $url = self::HOST . $api_key . '/get/V32';
    }
    try {
      $client = $this->httpClient;
      $response = $client->request($method, $url, $options);
      $code = $response->getStatusCode();
      $api_key = $this->getApiKey();
      // The x_api_header must be sent with the request, typically this is set
      // in IFTTT.
      if (
        $code == 200 &&
        isset($api_key) &&
        isset($_SERVER["HTTP_X_API_KEY"]) &&
        $_SERVER["HTTP_X_API_KEY"] == $api_key
      ) {
        $response_body = $response->getBody()->getContents();
        if ($response_body === '["1"]') {
          $result = 'off';
        }
        if ($response_body === '["2"]') {
          $result = 'on';
        }
      }
    }
    catch (\Exception $error) {
      // Add a mail notification to the owner when this request fails.
      $this->loggerFactory->error($error->getMessage());
    }

    return $result;
  }

  /**
   * Enable or disable the device.
   *
   * @param string $device
   *   The device in question "fence" or "alarm".
   * @param string $operation
   *   The operation to perform "enable" or "disable".
   *
   * @return string
   *   The request status.
   */
  private function sendToggleRequest(string $device, string $operation) {
    $api_key = $this->getApiKey();
    if ($device == self::FENCE && isset($api_key)) {
      $url = self::HOST . $api_key . '/update/V1?value=1';
    }
    elseif ($device == self::ALARM && isset($api_key)) {
      $url = self::HOST . $api_key . '/update/V2?value=1';
    }
    else {
      $this->loggerFactory->error('Unknown device in sendToggleRequest');
    }
    $placeholders = ['@operation' => $operation, '@device' => $device];
    $result = $this->t('Cannot send request to @operation @device', $placeholders);
    $method = 'GET';
    $options = [];
    // Send the actual request to i2t server.
    try {
      $client = $this->httpClient;
      $response = $client->request($method, $url, $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        // Request body is always empty, nothing else to do just log the action.
        $result = $this->t('Request send to @operation @device', $placeholders);
      }
    }
    catch (\Exception $error) {
      $this->loggerFactory->error($error->getMessage());
    }

    return $result;
  }

  /**
   * Gets the API Key from the configuration form.
   *
   * @return mixed
   *   The API Key or FALSE
   */
  private function getApiKey() {
    $api_key = $this->i2tConfig->get('api_key');
    if (!empty($api_key)) {
      return $api_key;
    }

    return FALSE;
  }

}
