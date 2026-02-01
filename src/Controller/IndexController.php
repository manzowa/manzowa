<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Model\User;
use App\Service\UserRole;
use App\Service\UserStatus;


class IndexController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        // // $user = new User(
        // //     id: 1,
        // //     fullname: "John Doe",
        // //     username: "johndoe",
        // //     email: "john.doe@example.com",
        // //     password: "password123",
        // //     status: UserStatus::ACTIVE,
        // //     attempts: 0,
        // //     role: UserRole::PREMIUM,
        // //     metadata: []
        // // );

        // $dataUser = [
        //     'id' => 1,
        //     'fullname' => "John Doe",
        //     'username' => "johndoe",
        //     'email' => "john.doe@example.com",
        //     'password' => "password123",
        //     'status' => "active",
        //     'attempts' => 0,
        //     'role' => 1,
        //     'metadata' => [],
        //     'createdAt' => new \DateTime('2024-01-05 10:00:00'),
        //     'updatedAt' => new \DateTime('2024-01-05 12:00:00'),   
        // ];

        // $user = User::fromState($dataUser);
        // var_dump($user->toArray());
        // die();
        return $this->render($response, 'home/index.html.twig', []);
    }
}