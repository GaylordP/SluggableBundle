<?php

namespace GaylordP\SluggableBundle\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Sluggable
{
    /**
     * @var string
     */
    public $propertySluggable;
}
