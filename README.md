# ddev-gitlab-poc
POC to have DDEV and gitlab up&amp;running


The idea is to have a GITLAB instance running that is able to communicate with a DDEV project containing a Drupal 10 website that provides Oauth2/OpenID connect accounts; and those accounts are  able to do login in the gitlab instance.


So far, there are 3 approaches:

Approach 1
---
a docker-compose file loaded with `docker compose up` that have the gitlab instance in https://gitlab.ddev.site:port.
But also tried to combine with the ddev-router provided by the DDEV project (D10), as DDEV can handle urls via its traefik.
This could work IF i manage to have the port removed from the url, but i dont know how so far


Approach 2
---
All together in a DDEV project, but i did not manage to have the web service of ddev pointing to one url and the gitlab service pointing to a different one.


Approach 3
---
two different ddev projects: one for gitlab and one for accounts.
This is the approach that is close to a sucess, but i nthe end i got the error:
```Could not authenticate you from OpenIDConnect because "Ssl connect returned=1 errno=0 peeraddr=192.168.16.6:443 state=error: certificate verify failed (unable to get local issuer certificate)". ``` 
after being redirected from the login page.
