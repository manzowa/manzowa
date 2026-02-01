<?php
### ───────────────────────────────────────────────
### @route Api
### ───────────────────────────────────────────────
$app->group('/api/v1', function ($group) {
    // Action test
    $group->get(
        '/items',
        [App\Controller\Api\V1\DocumentController::class, 'test']
    )->setName('items.index');
    // Action Docs
    $group->get(
        '/docs',
        [App\Controller\Api\V1\DocumentController::class, 'getDocAction']
    )->setName('doc.index');
    // Action Sessions
    $group->group('/token', function ($group) {
        $group->post(
            '',
            [App\Controller\Api\V1\TokenController::class, "postTokenAction"]
        )->setName('token.post')
            ->add(new App\Middleware\JsonMiddleware())
            ->add(new App\Middleware\JsonBodyParserMiddleware())
            ->add(new App\Middleware\JsonUserBodyMiddleware());
        $group->options(
            '',
            [App\Controller\Api\V1\TokenController::class, 'optionsTokenAction']
        )->setName('token.options');
        $group->delete(
            '/{id:[0-9]+}',
            [App\Controller\Api\V1\TokenController::class, 'deleteTokenAction']
        )->setName('token.delete')
            ->add(new App\Middleware\AuthMiddleware());
        $group->patch(
            '/{id:[0-9]+}',
            [App\Controller\Api\V1\TokenController::class, 'patchTokenAction']
        )->setName('token.patch')
            ->add(new App\Middleware\JsonMiddleware())
            ->add(new App\Middleware\JsonBodyParserMiddleware())
            ->add(new App\Middleware\JsonTokenBodyMiddleware())
            ->add(new App\Middleware\AuthMiddleware());
    });

    // Action schools
    $group->group('/ecoles', function ($group) {
        $group->get('', [
            App\Controller\Api\V1\School\IndexController::class,
            'getSchoolsAction'
        ])->setName('ecoles.index');
        // Post Ecole
        $group->post('', [
            App\Controller\Api\V1\School\IndexController::class,
            'postSchoolsAction'
        ])->setName('ecoles.post')
            ->add(new App\Middleware\JsonMiddleware())
            ->add(new App\Middleware\JsonBodyParserMiddleware())
            ->add(new App\Middleware\JsonSchoolBodyMiddleware())
            ->add(new App\Middleware\AuthMiddleware());
        //Action school by Page
        // With just page
        $group->get('/page/{page:[0-9]+}', [
            \App\Controller\Api\V1\School\IndexController::class,
            'getSchoolsByAction'
        ])->setName('ecoles.page.basic');
        // With just page and limit
        $group->get('/page/{page:[0-9]+}/{offset:[0-9]+}', [
            \App\Controller\Api\V1\School\IndexController::class,
            'getSchoolsByAction'
        ])->setName('ecoles.page.limit');
        //  With just 'nom'
        $group->get('/page/{page:[0-9]+}/{offset:[0-9]+}/{nom}', [
            \App\Controller\Api\V1\School\IndexController::class,
            'getSchoolsByAction'
        ])->setName('ecoles.page.nom');
        // With all parameters
        $group->get('/page/{page:[0-9]+}/{offset:[0-9]+}/{nom}/{type}', [
            \App\Controller\Api\V1\School\IndexController::class,
            'getSchoolsByAction'
        ])->setName('ecoles.page.full');
        // Action school by ID
        $group->group('/{id:[0-9]+}', function ($group) {
            $group->get('', [
                App\Controller\Api\V1\School\IndexController::class,
                'getSchoolAction'
            ])->setName('ecole.get');
            $group->put('', [
                App\Controller\Api\V1\School\IndexController::class,
                'putSchoolAction'
            ])->setName('ecole.put')
                ->add(new App\Middleware\JsonMiddleware())
                ->add(new App\Middleware\JsonBodyParserMiddleware())
                ->add(new App\Middleware\JsonSchoolBodyMiddleware())
                ->add(new App\Middleware\AuthMiddleware());
            $group->patch('', [
                App\Controller\Api\V1\School\IndexController::class,
                'patchSchoolAction'
            ])->setName('ecole.patch')
                ->add(new App\Middleware\JsonMiddleware())
                ->add(new App\Middleware\JsonBodyParserMiddleware())
                ->add(new App\Middleware\JsonSchoolBodyPartialMiddleware())
                ->add(new App\Middleware\AuthMiddleware());
            $group->delete('', [
                App\Controller\Api\V1\School\IndexController::class,
                'deleteSchoolAction'
            ])->setName('ecole.delete')
                ->add(new App\Middleware\AuthMiddleware());

            // Action Adressses (sous-groupe d’écoles)
            $group->group('/adresses', function ($group) {
                $group->get('', [
                    App\Controller\Api\V1\School\AddressController::class,
                    'getAdressesAction'
                ])->setName('adresses.index');
                // Adresse POST
                $group->post('', [
                    App\Controller\Api\V1\School\AddressController::class,
                    'postAdressesAction'
                ])->setName('adresses.post')
                    ->add(new App\Middleware\JsonMiddleware())
                    ->add(new App\Middleware\JsonBodyParserMiddleware())
                    ->add(new App\Middleware\JsonAddressBodyMiddleware())
                    ->add(new App\Middleware\AuthMiddleware());
                $group->group('/{adresseid:[0-9]+}', function ($group) {
                    $group->get('', [
                        App\Controller\Api\V1\School\AddressController::class,
                        'getAdresseAction'
                    ])->setName('address.index');
                    $group->put('', [
                        App\Controller\Api\V1\School\AddressController::class,
                        'putAdresseAction'
                    ])->setName('address.put')
                        ->add(new App\Middleware\JsonMiddleware())
                        ->add(new App\Middleware\JsonBodyParserMiddleware())
                        ->add(new App\Middleware\JsonAddressBodyMiddleware())
                        ->add(new App\Middleware\AuthMiddleware());
                    $group->patch('', [
                        App\Controller\Api\V1\School\AddressController::class,
                        'patchAdresseAction'
                    ])->setName('address.patch')
                        ->add(new App\Middleware\JsonMiddleware())
                        ->add(new App\Middleware\JsonBodyParserMiddleware())
                        ->add(new App\Middleware\JsonAddressBodyPartialMiddleware())
                        ->add(new App\Middleware\AuthMiddleware());
                    $group->delete('', [
                        App\Controller\Api\V1\School\AddressController::class,
                        'deleteAdresseAction'
                    ])->setName('address.delete')
                        ->add(new App\Middleware\AuthMiddleware());
                });
            });

            // Action Horaires
            $group->group('/horaires', function ($group) {
                $group->get('', [
                    App\Controller\Api\V1\School\ScheduleController::class,
                    'getSchedulesAction'
                ])->setName('schedules.index');
                $group->post('', [
                    App\Controller\Api\V1\School\ScheduleController::class,
                    'postSchedulesAction'
                ])->setName('schedules.post')
                    ->add(new App\Middleware\JsonMiddleware())
                    ->add(new App\Middleware\JsonBodyParserMiddleware())
                    ->add(new App\Middleware\JsonScheduleBodyMiddleware())
                    ->add(new App\Middleware\AuthMiddleware());
                $group->group('/{horaireid:[0-9]+}', function ($group) {
                    $group->get('', [
                        App\Controller\Api\V1\School\ScheduleController::class,
                        'getOneScheduleAction'
                    ])->setName('schedule.index');
                    $group->delete('', [
                        App\Controller\Api\V1\School\ScheduleController::class,
                        'deleteScheduleAction'
                    ])->setName('schedule.delete')
                        ->add(new App\Middleware\AuthMiddleware());
                    $group->put('', [
                        App\Controller\Api\V1\School\ScheduleController::class,
                        'putScheduleAction'
                    ])->setName('schedule.put')
                        ->add(new App\Middleware\JsonMiddleware())
                        ->add(new App\Middleware\JsonBodyParserMiddleware())
                        ->add(new App\Middleware\JsonScheduleBodyMiddleware())
                        ->add(new App\Middleware\AuthMiddleware());
                });
            });

            // Action Images
            $group->group('/images', function ($group) {
                $group->get('', [
                    App\Controller\Api\V1\School\ImageController::class,
                    'getImagesAction'
                ])->setName('images.index');
                $group->post('', [
                    App\Controller\Api\V1\School\ImageController::class,
                    'postImagesAction'
                ])->setName('images.post')
                    ->add(new App\Middleware\FormImageBodyMiddleware())
                    ->add(new App\Middleware\AuthMiddleware());
                $group->group('/{imageid:[0-9]+}', function ($group) {
                    $group->get('', [
                        App\Controller\Api\V1\School\ImageController::class,
                        'getImageAction'
                    ])->setName('image.index');
                    $group->delete('', [
                        App\Controller\Api\V1\School\ImageController::class,
                        'deleteImageAction'
                    ])->setName('image.delete')
                        ->add(new App\Middleware\AuthMiddleware());
                    // Attributes
                    $group->group('/attributes', function ($group) {
                        $group->get('', [
                            App\Controller\Api\V1\School\ImageController::class,
                            'getImageAttributesAction'
                        ])->setName('image.attributes.index');
                        $group->patch('', [
                            App\Controller\Api\V1\School\ImageController::class,
                            'patchImageAttributesAction'
                        ])->setName('image.attributes.patch')
                            ->add(new App\Middleware\JsonMiddleware())
                            ->add(new App\Middleware\JsonBodyParserMiddleware())
                            ->add(new App\Middleware\AuthMiddleware());
                    });
                });
            });

            // Action Evenements
            $group->group('/evenements', function ($group) {
                $group->get('', [
                    App\Controller\Api\V1\Event\IndexController::class,
                    'getEventsAction'
                ])->setName('evenements.index');
                $group->post('', [
                    App\Controller\Api\V1\Event\IndexController::class,
                    'postEventsAction'
                ])->setName('evenements.post')
                    ->add(new App\Middleware\JsonMiddleware())
                    ->add(new App\Middleware\JsonBodyParserMiddleware())
                    ->add(new App\Middleware\JsonEventsBodyMiddleware())
                    ->add(new App\Middleware\AuthMiddleware());
                $group->group('/{evenementid:[0-9]+}', function ($group) {
                    $group->get('', [
                        App\Controller\Api\V1\Event\IndexController::class,
                        'getEventAction'
                    ])->setName('event.index');
                    $group->put('', [
                        App\Controller\Api\V1\Event\IndexController::class,
                        'putEventAction'
                    ])->setName('event.put')
                        ->add(new App\Middleware\JsonMiddleware())
                        ->add(new App\Middleware\JsonBodyParserMiddleware())
                        ->add(new App\Middleware\JsonEventsBodyMiddleware())
                        ->add(new App\Middleware\AuthMiddleware());
                    $group->patch('', [
                        App\Controller\Api\V1\Event\IndexController::class,
                        'patchEventAction'
                    ])->setName('event.patch')
                        ->add(new App\Middleware\JsonMiddleware())
                        ->add(new App\Middleware\JsonBodyParserMiddleware())
                        ->add(new App\Middleware\JsonEventsBodyPartialMiddleware())
                        ->add(new App\Middleware\AuthMiddleware());
                    $group->delete('', [
                        App\Controller\Api\V1\Event\IndexController::class,
                        'deleteEventAction'
                    ])->setName('event.delete')
                        ->add(new App\Middleware\JsonMiddleware())
                        ->add(new App\Middleware\JsonBodyParserMiddleware())
                        ->add(new App\Middleware\AuthMiddleware());
                    // Action Images (sous-groupe d’évenements)
                    $group->group('/images', function ($group) {
                        $group->get('', [
                            App\Controller\Api\V1\Event\ImageController::class,
                            'getEventImagesAction'
                        ])->setName('event.images.index');
                        $group->post('', [
                            App\Controller\Api\V1\Event\ImageController::class,
                            'postEventImagesAction'
                        ])->setName('event.images.post')
                            ->add(new App\Middleware\FormImageBodyMiddleware())
                            ->add(new App\Middleware\AuthMiddleware());
                        $group->group('/{imageid:[0-9]+}', function ($group) {
                            $group->get('', [
                                App\Controller\Api\V1\Event\ImageController::class,
                                'getEventImageAction'
                            ])->setName('event.image.index');
                            $group->delete('', [
                                App\Controller\Api\V1\Event\ImageController::class,
                                'deleteEventImageAction'
                            ])->setName('event.image.delete')
                                ->add(new App\Middleware\AuthMiddleware());
                            // Action Images (sous-groupe d’attributes)
                            $group->group('/attributes', function ($group) {
                                $group->get('', [
                                    App\Controller\Api\V1\Event\ImageController::class,
                                    ' getEventImageAttributesAction'
                                ])->setName('image.attributes.index');
                                $group->patch('', [
                                    App\Controller\Api\V1\Event\ImageController::class,
                                    'patchImageAttributesAction'
                                ])->setName('image.attributes.patch')
                                ->add(new App\Middleware\JsonMiddleware())
                                ->add(new App\Middleware\JsonBodyParserMiddleware())
                                ->add(new App\Middleware\AuthMiddleware());
                            });
                        });
                    });
                });
            });
        });
        $group->get('/{nom:[a-zA-Z0-9_-]+}', [
            App\Controller\Api\V1\School\IndexController::class,
            'getNameAction'
        ])->setName('ecole.name');
        $group->get('/{nom:[a-zA-Z0-9_-]+}/{limit:[0-9]+}', [
            App\Controller\Api\V1\School\IndexController::class,
            'getNameLimitAction'
        ])->setName('ecole.nomAndLimit');
    });
    // Action Evenements (global)
    // GET /evenements
    $group->group('/evenements', function ($group) {
        $group->get('', [
            App\Controller\Api\V1\Event\IndexController::class,
            'getAllEventsAction'
        ])->setName('events.all.index');
        // GET /evenements/filtre/{datetime}
        $group->group('/filtre', function ($group) {
            $group->group('/{datetime}', function ($group) {
                $group->get('', [
                    App\Controller\Api\V1\Event\IndexController::class,
                    'getAllEventFilterByDatetimeAction'
                ])->setName('event.filter.nom');

               $group->get('/{ville:[a-zA-Z0-9_-]+}', [
                    App\Controller\Api\V1\Event\IndexController::class,
                    'getAllEventFilterByDatetimeAndTownAction'
                ])->setName('event.filter.ville');
            });
        });
        // GET /evenements/{id}
        $group->group('/{id:[0-9]+}', function ($group) {
            $group->get('', [
                App\Controller\Api\V1\Event\IndexController::class,
                'getAllEventByIdAction'
            ])->setName('event.allby.id');
        });
    });
    // Action coments
    $group->group('/commentaires', function ($group) {
        $group->get('', [
            App\Controller\Api\V1\CommentController::class,
            'getCommentsAction'
        ])->setName('comments.index');
        $group->post('', [
            App\Controller\Api\V1\CommentController::class,
            'postCommentsAction'
        ])->setName('comments.post')
            ->add(new App\Middleware\JsonMiddleware())
            ->add(new App\Middleware\JsonBodyParserMiddleware());
        
        $group->group('/{id:[0-9]+}', function ($group) {
            $group->get('', [
                App\Controller\Api\V1\CommentController::class,
                'getCommentByIdAction'
            ])->setName('comments.id');
            $group->put('', [
                App\Controller\Api\V1\CommentController::class,
                'putCommentByIdAction'
            ])->setName('comments.put')
                ->add(new App\Middleware\JsonMiddleware())
                ->add(new App\Middleware\JsonBodyParserMiddleware());
        });
    });

    // Action ratings
    $group->group('/notations', function ($group) {
        $group->get('', [
            App\Controller\Api\V1\RatingController::class,
            'getRatingsAction'
        ])->setName('ratings.index');
        $group->post('', [
            App\Controller\Api\V1\RatingController::class,
            'postRatingAction'
        ])->setName('ratings.post')
            ->add(new App\Middleware\JsonMiddleware())
            ->add(new App\Middleware\JsonBodyParserMiddleware());
    
        $group->group('/{id:[0-9]+}', function ($group) {
            $group->get('', [
                App\Controller\Api\V1\RatingController::class,
                'getRatingByIdAction'
            ])->setName('ratings.id');  
            $group->put('', [
                App\Controller\Api\V1\RatingController::class,
                'putRatingByIdAction'
            ])->setName('ratings.put')
                ->add(new App\Middleware\JsonMiddleware())
                ->add(new App\Middleware\JsonBodyParserMiddleware());
        });
    });
})->add(new App\Middleware\DatabaseConnectionMiddleware());
