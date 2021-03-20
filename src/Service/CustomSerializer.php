<?php declare(strict_types=1);

namespace App\Service;

use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CustomSerializer
{
    public Serializer $serializer;

    public function __construct()
    {
        $dateCallback = function ($dateTime) {
            return $dateTime instanceof DateTime
                ? $dateTime->format('Y-m-d H:i:s')
                : '';
        };

        $reflectionExtractor = new ReflectionExtractor();
//        $doctrineExtractor = new DoctrineExtractor();

        $normalizer = new ObjectNormalizer(
            new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader())),
            new CamelCaseToSnakeCaseNameConverter(),
            null,
            null,
//            new PropertyInfoExtractor(
//                [
//                    $reflectionExtractor,
////                    $doctrineExtractor
//                ],
//                // Type extractors
//                [
////                    $doctrineExtractor,
//                    $reflectionExtractor
//                ]
//            ),
            null,
            null,
            [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => static function (
                    $object
                ) {
                    return $object->getId();
                },
            ],
        );

        $dateNormalizer = new DateTimeNormalizer(['datetime_format' => 'Y-m-d H:i:s']);

        $this->serializer = new Serializer(
            [$dateNormalizer, $normalizer, new ArrayDenormalizer()],
            [new JsonEncoder()],
        );
    }
}
