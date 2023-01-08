<?php

namespace App\Service;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SerializerService
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    )
    {
    }

    public function deserialize($groupsToDeserialize, $request, $object): object
    {
        try {
            if ($groupsToDeserialize) {
                $context = DeserializationContext::create()->setGroups($groupsToDeserialize);
                $object = $this->serializer->deserialize($request->getContent(), $object, 'json', $context);
            } else {
                $object = $this->serializer->deserialize($request->getContent(), $object, 'json');
            }
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
        return $object;
    }

    public function serialize($groupsToSerialize, $object): string
    {
        try {
            if ($groupsToSerialize) {
                $context = SerializationContext::create()->setGroups($groupsToSerialize);
                $object = $this->serializer->serialize($object, 'json', $context);
            } else {
                $object = $this->serializer->serialize($object, 'json');
            }
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

    public function validate($object): ?string
    {
        $errors = $this->validator->validate($object);
        if (count($errors) > 0) {
            $errors = $this->serialize('errors', $errors);
            $errors = json_decode($errors, true);
            return json_encode($errors);
        }
        return null;
    }

    public function checkErrors($object): void
    {
        $errors = $this->validate($object);
        if ($errors) throw new HttpException(Response::HTTP_BAD_REQUEST, $errors);
    }
}