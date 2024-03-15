# ddev-gitlab-poc
POC to have DDEV and gitlab up&amp;running

Given a existing Gitlab instance in `https://<gitlab-url>` and a existing Drupal10 site in `https://<drupal-url>`, we want to use those Drupal10 accounts to be able to log in the gitlab instance via the simple_oauth module that provides an OAUTH server. The existing production version of the D10 site is already capable to provide OAUTH login to a WIKI (confluence) or other D10 websites (that contains the openid_connect client module or cas).

This POC is to test in a localhost enviroment the integration between the D10 site and a localhost instance of Gitlab before doing the deploy in its respective production servers.

The D10 website runs locally on https://oauth.ddev.site.
The gitlab instance runs locally on https://gitlab.ddev.site


Inside this POC we are going to find:

- This readme file.
- Folder oauth: contains a DDEV project with a basic drupal10 and the needed modules; a dump of the DB in a sql format and the config folder is in sync with the database provided. Inside its .ddev folder we find the config.yaml and we also have a docker-compose.gitlab.yaml that should add a gitlab service to the project.
The D10 contains all the contrib modules for oauth_server and a custom module to provide the REST endpoint `userinfo`.

- `ddev composer install` is required

Before doing `ddev start`, you might need to create the folder were gitlab will be: `sudo mkdir -p /srv/gitlab` as per indicated in the gitlab documentation (https://docs.gitlab.com/ee/install/docker.html#set-up-the-volumes-location). Instructions state to use also the environment variable $GITLAB_HOME inside the docker-compose file, but we have just used the path folder instead directly.

Running `ddev start` will create all the needed containers:
```
 Container ddev-oauth-web  Created
 Container ddev-oauth-db  Created
 Container ddev-oauth-gitlab  Created
 Container ddev-oauth-web  Started
 Container ddev-oauth-db  Started
 Container ddev-oauth-gitlab  Started
```

Once the containers are ready, the first time you can directly import the DB provided via drush (`ddev import-db --file=oauth.sql`).
If you import the database, you will have:
- two users (admin/admin and u1/u1) 
- 2 oauth2 clients already configured for oauth (one for oauth2 and another for openid), to be used by gitlab once it is running
- needed roles
- endpoints enabled and configured


The `docker-compose.gitlab.yaml` file contains the initial GITLAB_OMNIBUS_CONFIG:

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

The `gitlab.rb` config used (file located in `/srv/gitlab/config/gitlab.rb or /etc/gitlab/gitlab.rb inside the container) contains the same initial config and the credentials for login via the oauth server provided by drupal:
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

After any change done to the gitlab.rb file, gitlab needs to be reconfigured:
```
ddev ssh -s gitlab
gitlab-ctl reconfigure
```

After ddev has started, you can test both platforms are running in https://oauth.ddev.site and https://gitlab.ddev.site.

From gitlab then, we will try to log in using the existing drupal users:
- Go to https://gitlab.ddev.site
- click on the OIC button below 'or sign in with'
- you are redirected to https://oauth.ddev.site login page
- use `u1` as username and password (if you have imported the DB provided)
- grant access to the client
- you will be redirected to gitlab now. Here you should be logged in, but we got the following error:

```
Could not authenticate you from OpenIDConnect because "Ssl connect returned=1 errno=0 peeraddr=<local_ip>:443 state=error: certificate verify failed (unable to get local issuer certificate)".
``` 

We have also tried to copy the certificates found in .ddev/traefik/certs to the proper /srv/gitlab/config folder:
- .ddev/traefik/certs/oauth.key|crt  to /srv/gitlab/config/trusted-certs/oauth.ddev.site.key|crt
- .ddev/traefik/certs/gitlab.key|crt  to /srv/gitlab/config/ssl/gitlab.ddev.site.key|crt
just to see if that solves the error, but it seems it does not.


Doing `ddev ssh -s gitlab` on the DDEV gitlab project allow us to do ssh into the gitlab instance. From there, if we try `echo | /opt/gitlab/embedded/bin/openssl s_client -connect oauth.ddev.site:443` response but couple of errors such as:
```
CONNECTED(00000003)
depth=0 O = mkcert development certificate, OU = esn@DESKTOP-DHJE3SK
verify error:num=20:unable to get local issuer certificate
verify return:1
depth=0 O = mkcert development certificate, OU = esn@DESKTOP-DHJE3SK
verify error:num=21:unable to verify the first certificate
verify return:1
depth=0 O = mkcert development certificate, OU = esn@DESKTOP-DHJE3SK
verify return:1
```
