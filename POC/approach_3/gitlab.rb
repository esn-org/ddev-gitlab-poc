...
  gitlab_rails['omniauth_providers'] = [
  {
    name: "openid_connect",
    label: "ESN ACcounts - OI",
    args: {
      name: 'openid_connect',
      scope: ['oauth2_access_to_profile_information'],
      response_type: 'code',
      issuer: 'https://gitlab.ddev.site',
      discovery: false,
      client_auth_method: 'basic',
      client_options: {
        identifier: 'gitlab',
        secret: 'gitlab',
        redirect_uri: 'https://gitlab.ddev.site/users/auth/openid_connect/callback',
        userinfo_endpoint: "https://accounts.ddev.site/oauth/v1/userinfo",
        authorization_endpoint: "https://accounts.ddev.site/oauth/authorize",
        token_endpoint: "https://accounts.ddev.site/oauth/token"
      }
    }
  }
]
...
