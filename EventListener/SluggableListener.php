<?php

namespace GaylordP\SluggableBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use GaylordP\SluggableBundle\Annotation\Sluggable;
use GaylordP\SluggableBundle\Util\Slugger;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class SluggableListener
{
    private $annotationReader;
    private $accessor;

    public function __construct(Reader $annotationReader, PropertyAccessorInterface $accessor)
    {
        $this->annotationReader = $annotationReader;
        $this->accessor = $accessor;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->isSluggable($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->isSluggable($args);
    }

    private function isSluggable(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        $reflection = new \ReflectionClass(get_class($entity));

        foreach ($reflection->getProperties() as $property) {
            $annotation = $this->annotationReader->getPropertyAnnotation($property, Sluggable::class);

            if (
                null !== $annotation
                    &&
                (
                    !$args instanceof PreUpdateEventArgs
                        ||
                    (
                        $args instanceof PreUpdateEventArgs
                            &&
                        array_key_exists($annotation->propertySluggable, $args->getEntityChangeSet())
                    )
                )
            ) {
                $valueToSlug = $this->accessor->getValue($entity, $annotation->propertySluggable);

                if (null !== $valueToSlug) {
                    $valueSlug = Slugger::slugify($valueToSlug);

                    $qb = $entityManager
                        ->createQueryBuilder()
                        ->select('slg.' . $property->getName())
                        ->from($reflection->getName(), 'slg')
                        ->where('slg.' . $property->getName() . ' LIKE :slug')
                        ->setParameter('slug', $valueSlug . '%')
                        ->getQuery()
                        ->getResult()
                    ;

                    if (($count = count($qb)) !== 0) {
                        $valueSlug = $valueSlug . '-' . ($count + 1);
                    }

                    $this->accessor->setValue($entity, $property->getName(), $valueSlug);
                }
            }
        }
    }
}
