<?php

namespace Sbox\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class HelloController extends FOSRestController
{
    /**
     * @Route("/hello", methods={"GET"})
     * @SWG\Get(
     *     produces={"application/json"},
     *     @SWG\Response(
     *          response=200,
     *          description="Returns a hello world JSON response",
     *          @SWG\Schema(
     *               type="object",
     *               required={"hello"},
     *               @SWG\Property(
     *                   type="string",
     *                   property="hello",
     *                   example="world"
     *               )
     *          )
     *     )
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function helloAction(): Response
    {
        $data = ["hello" => "world"];
        $view = $this->view($data);
        return $this->handleView($view);
    }
}
