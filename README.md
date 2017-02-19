# lmao
Laravel Magento Admin OAuth package

## What is this even?
This is my Laravel package. 
My use-case was to authenticate Magento webshop administrators using my application.
I created this because I got really frustrated at Magento OAuthentication and the other OAuth packages did not fit my use-case.
Also: I never need to do this twice, just pull the package using composer, create middleware, add the lines in there and I'm done!


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
It looks for the right status in the users' session. If it does not find it, it'll redirect to an authentication route in lmao.


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
