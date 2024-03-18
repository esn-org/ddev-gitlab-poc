# ddev-gitlab-poc
POC to have DDEV and gitlab up&amp;running

Given a existing Gitlab instance in `https://<gitlab-url>` and a existing Drupal10 site in `https://<drupal-url>`, we want to use those Drupal10 accounts to be able to log in the gitlab instance via the simple_oauth module that provides an OAUTH server. The existing production version of the D10 site is already capable to provide OAUTH login to a WIKI (confluence) or other D10 websites (that contains the openid_connect client module or cas).

This POC is to test in a localhost enviroment the integration between the D10 site and a localhost instance of Gitlab before doing the deploy in its respective production servers.

The D10 website runs locally on https://oauth.ddev.site.
The gitlab instance runs locally on https://gitlab.ddev.site


Inside this POC we are going to find:

- This readme file.
- Folder oauth: contains a DDEV project with a basic Drupal10 and the needed modules; a dump of the DB in a sql format and the config folder is in sync with the database provided. Inside its .ddev folder we find the config.yaml and we also have a docker-compose.gitlab.yaml that should add a gitlab service to the project.
The D10 contains all the contrib modules for oauth_server and a custom module to provide the REST endpoint `userinfo` (more detailed than the default endpoint provided by the simple_oauth module).

The `config.yaml` file (inside `oauth/.ddev`) contains the details to start the Drupal10 site. In addition, it executes (via hooks) some additional commands needed for the Gitlab instance to run properly and avoid having issues with the certificates.

The `docker-compose.gitlab.yaml` file (inside `oauth/.ddev`) contains the details to start a gitlab instance reachabe via `gitlab.ddev.site`, and the initial config for that gitlab is already set in the `gitlab.rb` provided in `.ddev/gitlab/etc/gitlab/` folder and used in the volumes key in the `docker-compose.gitlab.yaml` file.

The `gitlab.rb` config used (file located in `.ddev/gitlab/etc/gitlab/gitlab.rb` or `/etc/gitlab/gitlab.rb` inside the container) contains the same initial config, the initial root password and 2 different providers for connecting with the Drupal10 website (oauth2 and openID). **Currently the openID provider does not work (see https://www.drupal.org/project/simple_oauth/issues/3257293 for more details)** but Oauth2 works like a charm.

Running `ddev start` will create all the needed containers:
```
 Container ddev-oauth-web  Created
 Container ddev-oauth-db  Created
 Container ddev-oauth-gitlab  Created
 Container ddev-oauth-web  Started
 Container ddev-oauth-db  Started
 Container ddev-oauth-gitlab  Started
 ...
```

**Once DDEV has started, you can test both platforms are running in https://oauth.ddev.site and https://gitlab.ddev.site.**

- `ddev composer install` is required, specially the first time we are running this project, so all the Drupal10 depencendies are installed.

For Drupal to work, the first time you can directly import the DB provided via drush (`ddev import-db --file=oauth.sql`).
If you import the database, you will have:
- two users (admin/admin and u1/u1),
- 2 oauth2 clients already configured for oauth (one for oauth2 and another for openID), to be used by gitlab once it is running,
- needed roles and permissions created and set,
- endpoints enabled and configured,
- etc...

**The root password for gitlab is:** `q0w9e8r7t6y5`

After any change done to the gitlab.rb file, gitlab needs to be reconfigured:
```
ddev ssh -s gitlab
gitlab-ctl reconfigure
```

If we want to log in in Gitlab using any of the normal Drupal users, we can:
- Go to https://gitlab.ddev.site
- click on the *OAUTH* button below 'or sign in with'
- you are redirected to https://oauth.ddev.site login page
- use `u1` as username and password (if you have imported the DB provided)
- grant access to the client
- you will be redirected to gitlab now. Here you should be logged in.
*Currently the OIC login provider does not work as mentioned before*


The small POC of how to have a working Drupal10 and a local version of Gitlab is up&running.
We can use this D10 platform also to log in any other website locally that uses Oauth2 such as another D10 website with the module `openid_connect` installed.
