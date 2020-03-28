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

            //Se è il primo timer della giornata lo marco primo
            $repositoryTimers=$this->getDoctrine()->getRepository(Timers::class);
            if($repositoryTimers->getTimerFirstDay($timerRequest['user_id']))
                $timer->setFirstCycle('yes');
            else
                $timer->setFirstCycle($timerRequest['first_cycle']);

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
     * @param $id
     * @return JsonResponse
     *
     * @Route("/api/v1/tomatos/{id}", methods={"get"})
     *
     * @SWG\Get(
     *      description="Recupera l'ultimo tomato dato l'id di un utente",
     *      @SWG\Parameter(
     *          name="id",
     *          description="id dell'utente",
     *          in="path",
     *          type="integer",
     *          required=true,
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Tomato trovato",
     *          @SWG\Schema(ref=@Model(type=Timers::class))
     *     ),
     *      @SWG\Response(
     *          response=400,
     *          description="Tomato non trovato",
     *     )
     * )
     * @SWG\Tag(name="Timers")
     *
     * */
    public function getTomato(int $id,Request $request)
    {
        $repository=$this->getDoctrine()->getRepository(Timers::class);
        $result=$repository->getTomatosFromUserId($id);

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

    /**
     * @param $id
     * @return JsonResponse
     *
     * @Route("/api/v1/nextTimer/{idUser}", methods={"get"})
     *
     * @SWG\Get(
     *      description="Controlla se un dato utente ha un timer calcolabile e se ha completato un ciclo",
     *      @SWG\Parameter(
     *          name="idUser",
     *          description="id dell'utente",
     *          in="path",
     *          type="integer",
     *          required=true,
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Prossimo timer e il pomodoroCycle",
     *          @SWG\Schema(ref=@Model(type=Timers::class))
     *     ),
     *      @SWG\Response(
     *          response=204,
     *          description="Prossimo timer non calcolabile",
     *     )
     * )
     * @SWG\Tag(name="Timers")
     *
     * */
    public function getStepCycle(int $idUser)
    {
        $cycle=[
            ['type'=>'tomato',
                'duration'=>"2"],
            ['type'=>'pause',
                'duration'=>"1"],
            ['type'=>'tomato',
                'duration'=>"2"],
            ['type'=>'pause',
                'duration'=>"1"],
            ['type'=>'tomato',
                'duration'=>"2"],
            ['type'=>'pause',
                'duration'=>"3"],
        ];

        $match=[];
        $repository=$this->getDoctrine()->getRepository(Timers::class);
        $result=$repository->getTomatosCycle($idUser);
        $i=0;
       
        //Primo, ma broken
        if(!$result)
        {
            return new JsonResponse($cycle[0],200);    
        }

        foreach($result as $arrayResult)
        {
            if($i==count($cycle)-1)
            {
                return new JsonResponse($cycle[0],200);
            }
            if( ($arrayResult['type']==$cycle[$i]['type'])&&($arrayResult['duration']==$cycle[$i]['duration']) )
            {
                $match=[];
                array_push($match,$cycle[$i+1]);
            }
            else{
                $match=[];
                break;
            }
            $i++;
        }

       if(count($match)==0)
           return new JsonResponse([],204);

        return new JsonResponse($match,200);
    }

    /**
     * @param $id
     * @return JsonResponse
     *
     * @Route("/api/v1/pomodoroCycle/{idUser}", methods={"get"})
     *
     * @SWG\Get(
     *      description="Controlla se un dato utente ha completato un ciclo",
     *      @SWG\Parameter(
     *          name="idUser",
     *          description="id dell'utente",
     *          in="path",
     *          type="integer",
     *          required=true,
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Ciclo completato",
     *     ),
     *      @SWG\Response(
     *          response=204,
     *          description="Ciclo non completato",
     *     )
     * )
     * @SWG\Tag(name="Timers")
     *
     * */
    public function getPomodoroCycle(int $idUser)
    {
        $cycle=[
            ['type'=>'tomato',
                'duration'=>"2"],
            ['type'=>'pause',
                'duration'=>"1"],
            ['type'=>'tomato',
                'duration'=>"2"],
            ['type'=>'pause',
                'duration'=>"1"],
            ['type'=>'tomato',
                'duration'=>"2"],
            ['type'=>'pause',
                'duration'=>"3"],
        ];

        $repository=$this->getDoctrine()->getRepository(Timers::class);
        $result=$repository->getCycle($idUser);
        $flag=false;
        $i=0;
    
        if(count($result)!=6)
            return new JsonResponse($flag,200);

        foreach($result as $arrayResult)
        {
            if( ($arrayResult['type']==$cycle[$i]['type'])&&($arrayResult['duration']==$cycle[$i]['duration']) )
            {
                $flag=true;
            }
            else{
                $flag=false;
                break;
            }

            $i++;
        }

        return new JsonResponse($flag,200);
    }

    /**
     * @param $id
     * @return JsonResponse
     *
     * @Route("/api/v1/lastEvent/{idUser}", methods={"get"})
     *
     * @SWG\Get(
     *      description="Controlla gli ultimi task di un utente",
     *      @SWG\Parameter(
     *          name="idUser",
     *          description="id dell'utente",
     *          in="path",
     *          type="integer",
     *          required=true,
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Timer complieti o rotti",
     *          @SWG\Schema(ref=@Model(type=Timers::class))
     *     ),
     *      @SWG\Response(
     *          response=204,
     *          description="Timer non trovati",
     *     )
     * )
     * @SWG\Tag(name="Timers")
     *
     * */
    public function getLastTimer(int $idUser)
    {
        $repository=$this->getDoctrine()->getRepository(Timers::class);
        $result=$repository->getlastEvent($idUser);

        if(!$result)
            return new JsonResponse([],204);

        return new JsonResponse($result,200);
    }
}
