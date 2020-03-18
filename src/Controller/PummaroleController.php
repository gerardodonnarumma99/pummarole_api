<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

use App\Entity\Timers;
use Doctrine\Bundle\DoctrineBundle\Repository\TimersEntityRepository;
use App\Entity\TimerType;
use Doctrine\Bundle\DoctrineBundle\Repository\TimerTypeEntityRepository;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\UserEntityRepository;

class PummaroleController extends AbstractController
{
    /**
     * @param
     * @return Response
     *
     * @Route("/api/v1/timer", methods={"post"})
     *
     * @SWG\Post(
     *      description="Crea un timer",
     *       @SWG\Parameter(
     *          name="body",
     *          description="Dati di un timer",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(ref=@Model(type=Timers::class))
     *     )
     *     ),
     * @SWG\Response(
     *          response=204,
     *          description="Timer creato",
     *     ),
     * @SWG\Response(
     *          response=400,
     *          description="Errore nella creazione del timer",
     *     )
     * )
     * @SWG\Tag(name="Timers")
     *
     * */
    public function addTimer(Request $request)
    {
        try{
            $timerRequest=json_decode($request->getContent(),1,512,JSON_THROW_ON_ERROR);

            $timer=new Timers();

            //Trovo l'utente
            $repository=$this->getDoctrine()->getRepository(User::class);
            $user=$repository->find($timerRequest['user_id']);
            $timer->setUser($user);

            //Setto le date
            $timer->setStartDate(new \DateTime($timerRequest['start_date']));
            if(empty($timerRequest['end_date']))
                $timer->setEndDate(null);
            else
                $timer->setEndDate(new \DateTime($timerRequest['end_date']));

            //Status
            $timer->setStatus($timerRequest['status']);

            //Trovo il TimerType
            $repository=$this->getDoctrine()->getRepository(TimerType::class);
            $timerType=$repository->find($timerRequest['timer_type']);
            $timer->setTimerType($timerType);

            //Title
            $timer->setTitle($timerRequest['title']);

            //Description
            $timer->setDescription($timerRequest['description']);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timer);
            $entityManager->flush();
            $jsonContent=$this->get('serializer')->serialize($timer,'json');

            return new Response($jsonContent,200);
        }
        catch(\Exception  $exception) {
            return new Response($exception->getMessage(), 400);
        }

    }

    /**
     * @param $id
     * @return JsonResponse
     *
     * @Route("/api/v1/timer/{id}", methods={"get"})
     *
     * @SWG\Get(
     *      description="Recupera l'ultimo timer dato l'id di un utente",
     *      @SWG\Parameter(
     *          name="id",
     *          description="id dell'utente",
     *          in="path",
     *          type="integer",
     *          required=true,
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Timer trovato",
     *          @SWG\Schema(ref=@Model(type=Timers::class))
     *     ),
     *      @SWG\Response(
     *          response=400,
     *          description="Timer non trovato",
     *     )
     * )
     * @SWG\Tag(name="Timers")
     *
     * */
    public function getTimer(int $id,Request $request)
    {
        $repository=$this->getDoctrine()->getRepository(Timers::class);
        $result=$repository->getTimersFromUserId($id);

        if(!$result)
           return new JsonResponse([],204);

        return new JsonResponse($result,200);
    }

    /**
     * @param
     * @return Response
     *
     * @Route("/api/v1/timer/{id}", methods={"put"})
     *
     * @SWG\Put(
     *      description="Modifica un timer dato l'id",
     *       @SWG\Parameter(
     *          name="body",
     *          description="Dati di un timer",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(ref=@Model(type=Timers::class))
     *     )
     *     ),
     * @SWG\Response(
     *          response=204,
     *          description="Timer modificato",
     *     ),
     * @SWG\Response(
     *          response=400,
     *          description="Errore nella modifica del timer",
     *     )
     * )
     * @SWG\Tag(name="Timers")
     *
     * */
    public function putTimer(int $id,Request $request)
    {
        try{
            $timerRequest=json_decode($request->getContent(),1,512,JSON_THROW_ON_ERROR);

            $entityManager=$this->getDoctrine()->getManager();
            $timer=$entityManager->getRepository(Timers::class)->find($id);
            if($timer==null)
                return new JsonResponse(null,400);

            //Trovo l'utente
            $repository=$this->getDoctrine()->getRepository(User::class);
            $user=$repository->find($timerRequest['user_id']);
            $timer->setUser($user);

            //Setto le date
            $timer->setStartDate(new \DateTime($timerRequest['start_date']));
            if(empty($timerRequest['end_date']))
                $timer->setEndDate(null);
            else
                $timer->setEndDate(new \DateTime($timerRequest['end_date']));

            //Status
            $timer->setStatus($timerRequest['status']);

            //Trovo il TimerType
            $repository=$this->getDoctrine()->getRepository(TimerType::class);
            $timerType=$repository->find($timerRequest['timer_type']);
            $timer->setTimerType($timerType);

            $entityManager->persist($timer);
            $entityManager->flush();
            $jsonContent=$this->get('serializer')->serialize($timer,'json');

            return new Response($jsonContent,200);
        }
        catch(\Exception  $exception) {
            return new Response($exception->getMessage(), 400);
        }
    }
}
