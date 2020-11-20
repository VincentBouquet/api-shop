<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class OrderController extends AbstractController
{
    /**
     * @Route("/api/order", name="order_index", methods={"GET"})
     */
    public function index(OrderRepository $repository): Response
    {
        $orders = $repository->findAll();
        return $this->json($orders,200,[],[
            "groups"=>["order_list"]
        ]);
    }

    /**
     * @route("/api/order/{order}", name="order_read", methods={"GET"})
     */
    public function read(Order $order)
    {
        return $this->json($order,200,[],["groups"=>["order_details"]]);
    }

    /**
     * @route("/api/order", name="order_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer)
    {
        if (!$request->getContent()){
            return $this->json(["error"=>"request content is required"],400);
        }
        /** @var Order $order */
        try {
            $order = $serializer->deserialize($request->getContent(), Order::class, "json");
        }catch (NotEncodableValueException $exception){
            return $this->json(["message"=>"Invalid json format"],400);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();
        return $this->json($order,200,[],["groups"=>["order_details"]]);
    }

    /**
     * @route("/api/order/{order}", name="order_edit", methods={"PUT"})
     */
    public function edit(Order $order, Request $request, SerializerInterface $serializer)
    {
        if (!$request->getContent()){
            return $this->json(["error"=>"request content is required"],400);
        }
        $serializer->deserialize($request->getContent(),Order::class,"json",["object_to_populate"=>$order]);

        $this->getDoctrine()->getManager()->flush();
        return $this->json($order,201);
    }

    /**
     * @route("/api/order/{order}", name="order_delete", methods={"DELETE"})
     */
    public function delete(Order $order)
    {
        $em=$this->getDoctrine()->getManager();
        $em->remove($order);
        $em->flush();
        return$this->json(["message"=>"Order deleted"]);
    }
}
