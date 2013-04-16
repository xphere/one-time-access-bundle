Berny\OneTimeAccessBundle
=========================

Do you ever wanted to authenticate your users in a Symfony2 application through a *one-time access url*?

Seek no more! This is your bundle! :D

Why I would want that?
----------------------

You can use one-time access urls for:
- Access to "Forgot your password?" forms
- [Password-less systems](http://notes.xoxco.com/post/27999787765/is-it-time-for-password-less-login)

Features
--------

- Customizable urls
- User defined token generation and retrieval
- Multiple firewalls

Compatibility
-------------
Tested under Symfony2 2.1.1

Installation
------------

### From [github](https://github.com)
- [Download the code](https://github.com/xphere/OneTimeAccessBundle) to `YourBundleDirectory/Berny/OneTimeAccessBundle`
- Add the bundle to your `AppKernel.php`

### From [composer/packagist](https://getcomposer.org)
Soon...

Usage
-----

Add `berny_ota` key in any firewall with, at least, a `route`.

```yml
security:
    firewalls:
        root:
            berny_ota:
                route: acme_myapp_ota
```

The current user provider must implement `OneTimeAccessBundle\Security\Provider\ProviderInterface`.

```yml
security:
    provider:
        users:
            entity:
                # AcmeMyAppBundle:UserRepository implements ProviderInterface
                class: AcmeMyAppBundle:User

    firewalls:
        root:
            provider: users
            berny_ota:
                route: acme_myapp_ota
```

You can set the `ota_provider` key to define a different service implementing the interface.

```yml
services:
    acme.myapp.ota.repository:
        class: Acme\\MyAppSecurity\\UserProvider

security:
    firewalls:
        root:
            berny_ota:
                route: acme_myapp_ota
                ota_provider: acme.myapp.ota.repository
```

By default, `route` must have a `_token` parameter to extract the one-time access token.

```yml
    acme_myapp_ota:
        pattern: ^/autologin/{_token}
        defaults: { _controller: AcmeMyAppBundle:Login:oneTimeAccess }
```

This can be customized with the `parameter` key.

```yml
security:
    firewalls:
        root:
            berny_ota:
                route: acme_myapp_ota
                parameter: otatoken
```

Of course, you can define your routes as always, using YAML, XML, annotations... you name it.

Token generation
----------------

This bundle doesn't cover token generation.
It's up to you to create unique tokens and link them to the user.

This could be part of a Doctrine implementation:
```php
class OTARepository extends EntityRepository implements ProviderInterface
{
    public function generateOTA($user)
    {
        $token = md5($user->getUsername() . time());
        $ota = new YourOneTimeAccessEntity($user, $token);
        $this->getEntityManager()->persist($ota);
        $this->getEntityManager()->flush($ota);
        return $ota;
    }

    public function loadUserByOTA($token)
    {
        $ota = $this->findOneByToken($token);
        if ($ota) {
            // Remember, user must be defined as EAGER in OTAEntity
            return $ota->getUser();
        }
    }

    public function invalidateByOTA($token)
    {
        $ota = $this->findOneByToken($token);
        $this->getEntityManager()->remove($ota);
        $this->getEntityManager()->flush();
    }
}
```

Route generation
----------------

Route generation is up to you too. Yes!
Are we being lazy, you say? Nope!
This means FULLY CUSTOMIZABLE routes for your one-time access links.

For example:
```php
$ota = $oneTimeAccessRepository->generateOnetimeAccess($user);
$url = $this->generateUrl('acme_myapp_ota', array(
    '_token' => $ota->getToken(),
));
```
