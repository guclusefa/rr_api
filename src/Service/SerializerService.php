<?php

namespace App\Service;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SerializerService
{
    public function __construct(
        private readonly SerializerInterface $serializer
    )
    {
    }

    public function deserialize($groupsToDeserialize, $request, $object): object
    {
        $context = DeserializationContext::create()->setGroups($groupsToDeserialize);
        try {
            $object = $this->serializer->deserialize($request->getContent(), $object, 'json', $context);
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
        return $object;
    }

    public function serialize($groupsToSerialize, $object): string
    {
        $context = SerializationContext::create()->setGroups($groupsToSerialize);
        try {
            $object = $this->serializer->serialize($object, 'json', $context);
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
        return $object;
    }

    public function getSerializedData($data): string
    {
        $data = ["data" => json_decode($data, true)];
        return json_encode($data);
    }
}