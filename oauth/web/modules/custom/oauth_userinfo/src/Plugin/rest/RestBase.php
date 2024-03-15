<?php

namespace Drupal\oauth_userinfo\Plugin\rest;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\oauth_userinfo\OauthUserManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RestBase.
 *
 * RestBase class for the Rest resources of the modules.
 */
class RestBase extends ResourceBase {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * A curent user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The oauth_userinfo.user_manager service.
   *
   * @var \Drupal\oauth_userinfo\OauthUserManagerInterface
   */
  protected $oauthUserManager;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user instance.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   * @param \Drupal\oauth_userinfo\OauthUserManagerInterface $oauth_user_manager
   *   The oauth_userinfo.user_manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, Request $current_request, OauthUserManagerInterface $oauth_user_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->currentRequest = $current_request;
    $this->entityTypeManager = $entity_type_manager;
    $this->oauthUserManager = $oauth_user_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('example_rest'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('oauth_userinfo.user_manager'),
    );
  }

  /**
   * Sets a response.
   *
   * @param array $message
   *   The response message.
   * @param int $cache
   *   Cache age.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Returns the response
   */
  protected function setResponse(array $message, $cache = 0) {
    $build = [
      '#cache' => [
        'max-age' => $cache,
      ],
    ];

    $response = new ResourceResponse($message);
    $response->addCacheableDependency($build);

    return $response;
  }

}
