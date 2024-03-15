# ddev-gitlab-poc
POC to have DDEV and gitlab up&amp;running

The idea is to have a GITLAB instance running that is able to communicate with a DDEV project containing a Drupal 10 website that provides Oauth2/OpenID connect accounts; and those accounts are able to do login in the gitlab instance.

In the `oauth` folder we will find a DDEV Drupal10 project with basic contrib modules and a single custom one that provides a custom `userinfo` endpoint. All the necesary config is in the `config/sync` folder and it should be able to install from that folderir import its config from there.
Oauth/openid is provided by simple_oauth + consumers, and the permissions for the endpoint should be ok if imported from config.

You just need to create a new user (so it is not tested with admin) and a new consumer in `https://oauth.ddev.site/admin/config/services/consumer`:
- redirect_url should be `https://gitlab.ddev.site/users/auth/openid_connect/callback` (for oauth)
- scope: select Oauth2 from the list (if D10 not installed from accounts, you should create a role with the machine name `oauth2_access_to_profile_information` for the purpose of this testing)

The consumer can be tested via postman with the following parameters:

```
redirect_uri: 'https://gitlab.ddev.site/users/auth/openid_connect/callback',
userinfo_endpoint: "https://oauth.ddev.site/oauth/v1/userinfo",
authorization_endpoint: "https://oauth.ddev.site/oauth/authorize",
token_endpoint: "https://oauth.ddev.site/oauth/token"
```

Inside the .ddev folder in `/oauth` we find the `docker-compose.external_links.yaml` to let ddev_router know about the second DDEV project.



The second DDEV project (gitlab) located in the folder `gitlab` contains inside its .ddev folder a `docker-compose.override.yaml` that will create a gitlab instance with the following GITLAB_OMNIBUS_CONFIG (probably partially wrong):

```
external_url 'https://gitlab.ddev.site'
nginx['redirect_http_to_https'] = false
letsencrypt['enable'] = false
nginx['listen_port'] = 80
nginx['listen_https'] = false
nginx['ssl_verify_client'] = "off"
gitlab_rails['omniauth_allow_single_sign_on'] = ['openid_connect']
gitlab_rails['omniauth_auto_link_ldap_user'] = true
gitlab_rails['omniauth_block_auto_created_users'] = true
nginx['ssl_certificate'] = "/etc/gitlab/ssl/gitlab.ddev.site.crt"
nginx['ssl_certificate_key'] = "/etc/gitlab/ssl/gitlab.ddev.site.key"
```
and the rest of the services configured (maybe some are wrong or not needed).

in addition, and before doing `ddev start`, you might need to create the gitlab folder: `sudo mkdir -p /srv/gitlab` as per (https://docs.gitlab.com/ee/install/docker.html#set-up-the-volumes-location). We dont use the environment variable $GITLAB_HOME inside the docker-compose image.


The `gitlab.rb` config used (file located in `/srv/gitlab/config/gitlab.rb or /etc/gitlab/gitlab.rb inside the container) is:
```
external_url 'https://gitlab.ddev.site'
nginx['redirect_http_to_https'] = false
letsencrypt['enable'] = false
nginx['listen_port'] = 80
nginx['listen_https'] = false
nginx['ssl_verify_client'] = "off"
nginx['ssl_client_certificate'] = "/etc/gitlab/ssl/ca.crt"
gitlab_rails['omniauth_allow_single_sign_on'] = ['openid_connect']
gitlab_rails['omniauth_auto_link_ldap_user'] = true
gitlab_rails['omniauth_block_auto_created_users'] = true
nginx['ssl_certificate'] = "/mnt/ddev_config/traefik/certs/gitlab.crt"
nginx['ssl_certificate_key'] = "/mnt/ddev_config/traefik/certs/gitlab.key"
gitlab_rails['omniauth_providers'] = [
  {
    name: "openid_connect",
    label: "OIC",
    args: {
      name: 'openid_connect',
      scope: ['oauth2_access_to_profile_information'],
      response_type: 'code',
      issuer: 'https://oauth.ddev.site',
      discovery: false,
      client_auth_method: 'basic',
      uid_field: "preferred_username",
      client_options: {
        identifier: 'gitlab',
        secret: 'gitlab',
        redirect_uri: 'https://gitlab.ddev.site/users/auth/openid_connect/callback',
        userinfo_endpoint: "https://oauth.ddev.site/oauth/v1/userinfo",
        authorization_endpoint: "https://oauth.ddev.site/oauth/authorize",
        token_endpoint: "https://oauth.ddev.site/oauth/token"
      }
    }
  }
]
```


With everything, the error gotten is 
`Could not authenticate you from OpenIDConnect because "Ssl connect returned=1 errno=0 peeraddr=192.168.160.5:443 state=error: certificate verify failed (unable to get local issuer certificate)". ` 

after clicking in the "OIC" button for login, being redirected to the OAUTH platform, login and granting the client and redirected back to gitlab.



Variation 1
---

We could have added inside accounts folder the `docker-compose.override.yaml` named as `docker-compose.gitlab.yaml` and avoid having 2 DDEV projects, but I did not manage to have 2 urls working within the same DDEV project (one for the D10 in web and another one for the gitlab instance), as both need access to port 443.

