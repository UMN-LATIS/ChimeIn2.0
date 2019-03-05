# Deploying ChimeIn

ChimeIn is available as an open source tool.  It's a PHP/MySQL application which leverages VueJS for frontend rendering.  It also relies on Redis and a Node based websocket tool called laravel-echo-server.

For authentication, ChimeIn is currently tied to SAML (Shibboleth) using a PHP library.  It could easily be extended to support other authentication platforms though. 

## Running it yourself

We're planning to make a Docker package available for deploying ChimeIn, but that's not available at this time.  We currently deploy ChimeIn with Ansible, and would be happy to share those playbooks.