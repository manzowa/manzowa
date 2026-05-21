<?php
### ───────────────────────────────────────────────
### @route Api
### ───────────────────────────────────────────────
use Slim\App;
use App\Controller\Api\V1\DocumentController;
use App\Controller\Api\V1\UserController;
use App\Controller\Api\V1\TokenController;
use App\Controller\Api\V1\School\IndexController as ApiSchoolIndexController;
use App\Controller\Api\V1\School\AddressController as ApiSchoolAddressController;
use App\Controller\Api\V1\School\ScheduleController as ApiSchoolScheduleController;
use App\Controller\Api\V1\School\ImageController as ApiSchoolImageController; 
use App\Controller\Api\V1\Event\IndexController as ApiEventIndexController;
use App\Controller\Api\V1\Event\ImageController as ApiEventImageController;
use App\Controller\Api\V1\CommentController;
use App\Controller\Api\V1\RatingController;
use App\Middleware\JsonMiddleware;
use App\Middleware\FormImageBodyMiddleware;
use App\Middleware\JsonEventsBodyMiddleware;
use App\Middleware\JsonEventsBodyPartialMiddleware;
use App\Middleware\JsonBodyParserMiddleware;
use App\Middleware\JsonUserBodyMiddleware;
use App\Middleware\JsonSchoolBodyMiddleware;
use App\Middleware\JsonAddressBodyMiddleware;
use App\Middleware\JsonTokenBodyMiddleware;
use App\Middleware\JsonAddressBodyPartialMiddleware;
use App\Middleware\JsonSchoolBodyPartialMiddleware;
use App\Middleware\JsonScheduleBodyMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\DatabaseConnectionMiddleware;


return function (App $app) {
    $app->group('/api/v1', function ($group) {
        // Action test
        $group->get('/items', [DocumentController::class, 'test'])
            ->setName('items.index');
        // Action Docs
        $group->get('/docs', [DocumentController::class, 'getDocAction'])
            ->setName('doc.index');
        // action Users
        $group->group('/users', function ($group) {
            $group->post('', [UserController::class, 'postUserAction'])
                ->setName('users.post')
                ->add(new JsonMiddleware())
                ->add(new JsonBodyParserMiddleware())
                ->add(new JsonUserBodyMiddleware());

        });
        // Action Sessions
        $group->group('/token', function ($group) {
            $group->post('', [TokenController::class, "postTokenAction"])
                ->setName('token.post')
                ->add(new JsonMiddleware())
                ->add(new JsonBodyParserMiddleware());
                // ->add(new App\Middleware\JsonUserBodyMiddleware());

            $group->options('', [TokenController::class, 'optionsTokenAction'])
                ->setName('token.options');

            $group->delete('/{id:[0-9]+}', [TokenController::class, 'deleteTokenAction'])
                ->setName('token.delete')
                ->add(new AuthMiddleware());

            $group->patch('/{id:[0-9]+}', [TokenController::class, 'patchTokenAction'])
                ->setName('token.patch')
                ->add(new JsonMiddleware())
                ->add(new JsonBodyParserMiddleware())
                ->add(new JsonTokenBodyMiddleware())
                ->add(new AuthMiddleware());
        });

        // Action schools
        $group->group('/ecoles', function ($group) {
            $group->get('', [ ApiSchoolIndexController::class, 'getSchoolsAction'])
                ->setName('ecoles.index');
            // Post Ecole
            $group->post('', [ApiSchoolIndexController::class, 'postSchoolsAction'])
                ->setName('ecoles.post')
                ->add(new JsonMiddleware())
                ->add(new JsonBodyParserMiddleware())
                ->add(new JsonSchoolBodyMiddleware())
                ->add(new AuthMiddleware());
            //Action school by Page
            // With just page
            $group->get('/page/{page:[0-9]+}', [ApiSchoolIndexController::class, 'getSchoolsByAction'])
                ->setName('ecoles.page.basic');
            // With just page and limit
            $group->get('/page/{page:[0-9]+}/{offset:[0-9]+}', [ApiSchoolIndexController::class, 'getSchoolsByAction'])
                ->setName('ecoles.page.limit');
            //  With just 'nom'
            $group->get('/page/{page:[0-9]+}/{offset:[0-9]+}/{nom}', [ApiSchoolIndexController::class,'getSchoolsByAction'])
                ->setName('ecoles.page.nom');
            // With all parameters
            $group->get('/page/{page:[0-9]+}/{offset:[0-9]+}/{nom}/{type}', [ApiSchoolIndexController::class, 'getSchoolsByAction'])
                ->setName('ecoles.page.full');
            // Action school by ID
            $group->group('/{id:[0-9]+}', function ($group) {
                $group->get('', [
                    ApiSchoolIndexController::class,
                    'getSchoolAction'
                ])->setName('ecole.get');
                $group->put('', [ApiSchoolIndexController::class, 'putSchoolAction'])
                    ->setName('ecole.put')
                    ->add(new JsonMiddleware())
                    ->add(new JsonBodyParserMiddleware())
                    ->add(new JsonSchoolBodyMiddleware())
                    ->add(new AuthMiddleware());
                $group->patch('', [ApiSchoolIndexController::class, 'patchSchoolAction'])
                    ->setName('ecole.patch')
                    ->add(new JsonMiddleware())
                    ->add(new JsonBodyParserMiddleware())
                    ->add(new JsonSchoolBodyPartialMiddleware())
                    ->add(new AuthMiddleware());
                $group->delete('', [ApiSchoolIndexController::class, 'deleteSchoolAction'])
                    ->setName('ecole.delete')
                    ->add(new AuthMiddleware());

                // Action Adressses (sous-groupe d’écoles)
                $group->group('/adresses', function ($group) {
                    $group->get('', [ApiSchoolAddressController::class, 'getAdressesAction'])
                        ->setName('adresses.index');
                    // Adresse POST
                    $group->post('', [ApiSchoolAddressController::class, 'postAdressesAction'])
                        ->setName('adresses.post')
                        ->add(new JsonMiddleware())
                        ->add(new JsonBodyParserMiddleware())
                        ->add(new JsonAddressBodyMiddleware())
                        ->add(new AuthMiddleware());
                    $group->group('/{adresseid:[0-9]+}', function ($group) {
                        $group->get('', [ApiSchoolAddressController::class, 'getAdresseAction'])
                            ->setName('address.index');
                        $group->put('', [ApiSchoolAddressController::class, 'putAdresseAction'])
                            ->setName('address.put')
                            ->add(new JsonMiddleware())
                            ->add(new JsonBodyParserMiddleware())
                            ->add(new JsonAddressBodyMiddleware())
                            ->add(new AuthMiddleware());
                        $group->patch('', [ApiSchoolAddressController::class, 'patchAdresseAction'])
                            ->setName('address.patch')
                            ->add(new JsonMiddleware())
                            ->add(new JsonBodyParserMiddleware())
                            ->add(new JsonAddressBodyPartialMiddleware())
                            ->add(new AuthMiddleware());
                        $group->delete('', [ApiSchoolAddressController::class, 'deleteAdresseAction'])
                            ->setName('address.delete')
                            ->add(new AuthMiddleware());
                    });
                });

                // Action Horaires
                $group->group('/horaires', function ($group) {
                    $group->get('', [ApiSchoolScheduleController::class, 'getSchedulesAction'])
                        ->setName('schedules.index');
                    $group->post('', [ApiSchoolScheduleController::class, 'postSchedulesAction'])
                        ->setName('schedules.post')
                        ->add(new JsonMiddleware())
                        ->add(new JsonBodyParserMiddleware())
                        ->add(new JsonScheduleBodyMiddleware())
                        ->add(new AuthMiddleware());
                    $group->group('/{horaireid:[0-9]+}', function ($group) {
                        $group->get('', [ApiSchoolScheduleController::class, 'getOneScheduleAction'])
                            ->setName('schedule.index');
                        $group->delete('', [ApiSchoolScheduleController::class, 'deleteScheduleAction'])
                            ->setName('schedule.delete')
                            ->add(new AuthMiddleware());
                        $group->put('', [ApiSchoolScheduleController::class, 'putScheduleAction'])
                            ->setName('schedule.put')
                            ->add(new JsonMiddleware())
                            ->add(new JsonBodyParserMiddleware())
                            ->add(new JsonScheduleBodyMiddleware())
                            ->add(new AuthMiddleware());
                    });
                });

                // Action Images
                $group->group('/images', function ($group) {
                    $group->get('', [ApiSchoolImageController::class, 'getImagesAction'])
                        ->setName('images.index');
                    $group->post('', [ApiSchoolImageController::class, 'postImagesAction'])
                        ->setName('images.post')
                        ->add(new FormImageBodyMiddleware())
                        ->add(new AuthMiddleware());
                    $group->group('/{imageid:[0-9]+}', function ($group) {
                        $group->get('', [ApiSchoolImageController::class, 'getImageAction'])
                            ->setName('image.index');
                        $group->delete('', [ApiSchoolImageController::class, 'deleteImageAction'])
                            ->setName('image.delete')
                            ->add(new AuthMiddleware());
                        // Attributes
                        $group->group('/attributes', function ($group) {
                            $group->get('', [ApiSchoolImageController::class, 'getImageAttributesAction'])
                                ->setName('image.attributes.index');
                            $group->patch('', [ApiSchoolImageController::class, 'patchImageAttributesAction'])
                                ->setName('image.attributes.patch')
                                ->add(new JsonMiddleware())
                                ->add(new JsonBodyParserMiddleware())
                                ->add(new AuthMiddleware());
                        });
                    });
                });

                // Action Evenements
                $group->group('/evenements', function ($group) {
                    $group->get('', [ApiEventIndexController::class, 'getEventsAction'])
                        ->setName('evenements.index');
                    $group->post('', [ApiEventIndexController::class, 'postEventsAction'])
                        ->setName('evenements.post')
                        ->add(new JsonMiddleware())
                        ->add(new JsonBodyParserMiddleware())
                        ->add(new JsonEventsBodyMiddleware())
                        ->add(new AuthMiddleware());
                    $group->group('/{evenementid:[0-9]+}', function ($group) {
                        $group->get('', [ApiEventIndexController::class, 'getEventAction'])
                            ->setName('event.index');
                        $group->put('', [ApiEventIndexController::class, 'putEventAction'])
                            ->setName('event.put')
                            ->add(new JsonMiddleware())
                            ->add(new JsonBodyParserMiddleware())
                            ->add(new JsonEventsBodyMiddleware())
                            ->add(new AuthMiddleware());
                        $group->patch('', [ApiEventIndexController::class, 'patchEventAction'])
                            ->setName('event.patch')
                            ->add(new JsonMiddleware())
                            ->add(new JsonBodyParserMiddleware())
                            ->add(new JsonEventsBodyPartialMiddleware())
                            ->add(new AuthMiddleware());
                        $group->delete('', [ApiEventIndexController::class, 'deleteEventAction'])
                            ->setName('event.delete')
                            ->add(new JsonMiddleware())
                            ->add(new JsonBodyParserMiddleware())
                            ->add(new AuthMiddleware());

                        // Action Images (sous-groupe d’évenements)
                        $group->group('/images', function ($group) {
                            $group->get('', [ApiEventImageController::class, 'getEventImagesAction'])
                                ->setName('event.images.index');
                            $group->post('', [ApiEventImageController::class, 'postEventImagesAction'])
                                ->setName('event.images.post')
                                ->add(new FormImageBodyMiddleware())
                                ->add(new AuthMiddleware());
                            $group->group('/{imageid:[0-9]+}', function ($group) {
                                $group->get('', [ApiEventImageController::class, 'getEventImageAction'])
                                    ->setName('event.image.index');
                                $group->delete('', [ApiEventImageController::class,'deleteEventImageAction'])
                                    ->setName('event.image.delete')
                                    ->add(new AuthMiddleware());
                                // Action Images (sous-groupe d’attributes)
                                $group->group('/attributes', function ($group) {
                                    $group->get('', [ApiEventImageController::class,'getEventImageAttributesAction'])
                                        ->setName('image.attributes.index');
                                    $group->patch('', [ApiEventImageController::class, 'patchImageAttributesAction'])
                                        ->setName('image.attributes.patch')
                                        ->add(new JsonMiddleware())
                                        ->add(new JsonBodyParserMiddleware())
                                        ->add(new AuthMiddleware());
                                });
                            });
                        });
                    });
                });
            });
            $group->get('/{nom:[a-zA-Z0-9_-]+}', [ApiSchoolIndexController::class, 'getNameAction'])
                ->setName('ecole.name');
            $group->get('/{nom:[a-zA-Z0-9_-]+}/{limit:[0-9]+}', [ApiSchoolIndexController::class, 'getNameLimitAction'])
                ->setName('ecole.nomAndLimit');
        });
        // Action Evenements (global)
        // GET /evenements
        $group->group('/evenements', function ($group) {
            $group->get('', [ApiEventIndexController::class, 'getAllEventsAction'])
                ->setName('events.all.index');
            // GET /evenements/filtre/{datetime}
            $group->group('/filtre', function ($group) {
                // Filter By Datetime
                $group->get('/datetime/{datetime}', [
                    ApiEventIndexController::class, 'getAllEventFilterByDatetimeAction'
                ])->setName('event.filter.datetime');
                // Filter By Town
                $group->get('/ville/{ville:[a-zA-Z0-9_-]+}', [
                    ApiEventIndexController::class, 'getAllEventFilterByTownAction'
                ])->setName('event.filter.town');

                // Filter Datetime and Ville
                $group->get('/datetime/{datetime}/ville/{ville:[a-zA-Z0-9_-]+}', [
                    ApiEventIndexController::class, 'getAllEventFilterByDatetimeAndTownAction'
                ])->setName('event.filter.datetime.town');
            });
            // GET /evenements/{id}
            $group->group('/{id:[0-9]+}', function ($group) {
                $group->get('', [ApiEventIndexController::class, 'getAllEventByIdAction'])
                ->setName('event.allby.id');
            });
        });

        // Action comments
        $group->group('/comments', function ($group) {
            $group->get('', [CommentController::class, 'getCommentsAction'])
                ->setName('comments.index');
            $group->post('', [CommentController::class,'postCommentsAction'])
                ->setName('comments.post')
                ->add(new JsonMiddleware())
                ->add(new JsonBodyParserMiddleware())
                ->add(new AuthMiddleware());
            
            $group->group('/{id:[0-9]+}', function ($group) {
                $group->get('', [CommentController::class, 'getCommentByIdAction'])
                    ->setName('comments.id');
                $group->put('', [CommentController::class, 'putCommentByIdAction'])
                    ->setName('comments.put')
                    ->add(new JsonMiddleware())
                    ->add(new JsonBodyParserMiddleware())
                    ->add(new AuthMiddleware());
            });
        });

        // Action ratings
        $group->group('/ratings', function ($group) {
            $group->get('', [RatingController::class, 'getRatingsAction'])
                ->setName('ratings.index');
            $group->post('', [RatingController::class, 'postRatingAction'])
                ->setName('ratings.post')
                ->add(new JsonMiddleware())
                ->add(new JsonBodyParserMiddleware())
                ->add(new AuthMiddleware());
        
            $group->group('/{id:[0-9]+}', function ($group) {
                $group->get('', [RatingController::class, 'getRatingByIdAction'])
                    ->setName('ratings.id');  
                $group->put('', [RatingController::class, 'putRatingByIdAction'])
                    ->setName('ratings.put')
                    ->add(new JsonMiddleware())
                    ->add(new JsonBodyParserMiddleware())
                    ->add(new AuthMiddleware());
            });
        });
    })->add(new DatabaseConnectionMiddleware());
};