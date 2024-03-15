<?php

namespace Drupal\oauth_userinfo\Plugin\rest\resource;

use Drupal\oauth_userinfo\Plugin\rest\RestBase;

/**
 * Provides an endpoint for the UserInfo requested via Oauth2.
 *
 * It returns info and roles of the user.
 *
 * @RestResource(
 *   id="userinfo_endpoint",
 *   label=@Translation("UserInfo endpoint for oauth"),
 *   uri_paths={
 *     "canonical": "/oauth/v1/userinfo"
 *   }
 * )
 */
class UserInfoRest extends RestBase {

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Returns a ResourceResponse for the request.
   */
  public function get() {
    // If the user is not meant for oauth endpoint.
    if (empty($this->currentUser->id()) || $this->currentUser->isAnonymous()) {
      $message = ['Access forbidden'];

      return $this->setResponse($message);
    }

    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    if ($user === NULL) {
      $message = ['User not found or not valid.'];
      return $this->setResponse($message);
    }

    // As we have an user, we get its data for the response.
    $data = $this->oauthUserManager->generatePersonalInfo($user);
    $group_roles = $this->oauthUserManager->generateUserGroupRoles($user);
    // And retrieve all the roles we have generated in the previous function.
    $data['detailed_groups'] = $group_roles['full'];
    $data['groups'] = $group_roles['simplified'];

    // Finally we generate the response for the endpoint.
    return $this->setResponse($data);
  }

}
