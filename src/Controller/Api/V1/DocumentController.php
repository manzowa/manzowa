<?php 

/**
 * File DocumentController
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App\Controller\Api\V1
 * @package  App\Controller\Api\V1
 * @author   Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */
namespace App\Controller\Api\V1
{
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Views\Twig;

    class DocumentController extends \App\Controller\ApiController
    {
        private Twig $twig ;

        public function __construct(Twig $twig)
        {
            $this->twig = $twig;
        }



        /**
         * Method getDocAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * 
         * @return mixed
         */
        public function test(Request $request,  Response $response): Response 
        {
            $data = ['name' => 'New Item', 'price' => 19.99];
            return $this->response(true, 'Test successful', $data, 200);
        }
        /**
         * Method getDocAction [GET]
         *
         * @param Request $request
         * @param Response $response
         * 
         * @return mixed
         */
        public function getDocAction(
            Request $request,  
            Response $response
        ): Response {
            $data = ['name' => 'Page data'];
            return $this->twig->render($response, 'doc/index.html.twig', [
                'code' => 100,
                'message' => "ok"
            ])->withStatus(500);
        }
    }
}