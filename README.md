# lmao
Laravel Magento Admin OAuth package

## What is this even?
This is my Laravel package. I created it because I got really frustrated at Magento OAuthentication for Magento 1 webshops.
The other OAuth packages did not fit my use-case, and after this I never want to bother with it again. So I made this package, where you can just
add it using composer and be done with it.


## So how do i use this?

### Installation
```
composer require "topster21/lmao"
```

Also: add these in your **config/app.php**
#### Providers:

`Topster21\LMAO\LMAOServiceProvider::class,`


#### Aliases:

`'LMAO' => Topster21\LMAO\Facades\LMAO::class,`


### Application specifics
Send the user to **/lmao/initiate** to start the log-in process.
When authenticated, lmao will attempt to redirect the user to the original request page. If that fails the user will land on the homepage.
This is best used with Laravel Middleware, which is what it was designed for in the first place.

Make sure these values are present in your .env file:
```
LMAO_KEY=           (Set this in your Magento admin panel)
LMAO_SECRET=        (Set this in your Magento admin panel)
LMAO_URL=           (NO TRAILING /)
LMAO_ADMIN_SLUG=    (Optional, when your /admin/ slug is different, set it here. NO / in this)
```

The application stores values in the session. You can get them by using the following keys:


Grabs the status. Look [here](https://github.com/topster21/lmao/blob/develop/lmao/src/Status.php) for some hints.

`session('lmao_status') `

Grabs the token graciously given to us by the Magento application. It proves we logged in.

`session('lmao_token') `

Grabs the token secret graciously given to us by the Magento application. It proves we logged in.

`session('lmao_secret') `


### Example of usage
I have created a [middleware](https://laravel.com/docs/5.4/middleware#defining-middleware) class in my application. 
It puts the resulting token, secret and status

```php
public function handle($request, Closure $next)
{
    if (session()->has('lmao_status') && session('lmao_status') == Status::HAS_ACCESSTOKEN)
         // This glorious individual is allowed in! Praise
         return $next($request);
         
    // Send the filthy unauthorized user to the auth page
    return redirect()->guest('/lmao/initiate');
}
```


## P.S.
It currently does not catch deny-callbacks from the magento application. You'll need to catch those yourself.
