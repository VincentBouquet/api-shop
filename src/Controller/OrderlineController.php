<?php

namespace App\Controller;

use App\Entity\OrderLine;
use App\Repository\OrderLineRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class OrderlineController extends AbstractController
{
    /**
     * @Route("/api/orderline", name="orderline_index", methods={"GET"})
     */
    public function index(OrderLineRepository $repository): Response
    {
        $orderLines = $repository->findAll();
        return $this->json($orderLines,200,[],["groups"=>["orderline_list"]]);
    }

    /**
     * @Route ("/api/orderline", name="orderline_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer, DecoderInterface $decoder, OrderRepository $repository)
    {
        if (!$request->getContent()){
            return $this->json(["error" => "request content is required"], 400);
        }
        /** @var OrderLine $orderLine */
        try {
            $orderLine=$serializer->deserialize($request->getContent(),OrderLine::class, "json");
        } catch (NotEncodableValueException $exception) {
            return $this->json(["message" => "Invalid json format"],400);
        }

        $data = $decoder->decode($request->getContent(),"json");
        $orderId = $data["orderId"];
        $order = $repository->find($orderId);
        $orderLine->setOrderId($order);

        $em = $this->getDoctrine()->getManager();
        $em->persist($orderLine);
        $em->flush();
        return $this->json($orderLine,200,[],["groups"=>["orderline_details"]]);
    }

    /**
     * @Route ("/api/orderline/{orderline}", name="orderline_edit", methods={"PUT"})
     */
    public function edit(OrderLine $orderLine, Request $request, SerializerInterface $serializer)
    {
        if (!$request->getContent()){
            return $this->json(["error" => "request content is required"], 400);
        }
        $serializer->deserialize($request->getContent(),OrderLine::class,"json",["object_to_populate"=>$orderLine]);

        $this->getDoctrine()->getManager()->flush();;
        return $this->json([$orderLine,201]);
    }

    /**
     * @Route ("/api/orderline/{orderline}", name="orderline_delete", methods={"DELETE"})
     */
    public function delete(OrderLine $orderLine)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($orderLine);
        $em->flush();
        return $this->json(["message" => "OrderLine deleted"]);
    }
}
