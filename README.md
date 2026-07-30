# Mezzio Google Analytics Measurement Protocol #
Mezzio Middleware for Google ReCaptcha V3

### Composer ###

`composer require advancedideasmechanics/mezzio-recaptcha-v3-middleware`

#### Use ####

For route.php Middleware use.

`use AdvancedIdeasMechanics\MezzioReCaptchaV3\Middleware\ReCaptchaMiddleware::class;`

`$app->get('/', [ReCaptchaMiddleware:class, App\Handler\HomePageHandler::class], 'home');`

For use with Oauth2 Example

`$app->post('/oauth/authorize', [
     // Uses the withAction helper to change the expected Google action
     fn($container) => $container->get(ReCaptchaMiddleware::class)->withAction('oauth_login'),
     App\Handler\OAuthAuthorizationHandler::class,
 ], 'oauth.authorize');`

 #### Config ####

 `return [
     'recaptcha' => [
         'site_key'        => '6Lxxxx...',
         'secret_key'      => '6Lxxxx...',
         'score_threshold' => 0.5,
     ],
 ];`