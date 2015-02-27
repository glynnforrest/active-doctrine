<?php

namespace ActiveDoctrine\Entity\Traits;

/**
 * SlugTrait
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
trait SlugTrait
{
    /**
     * Configure slugs with the $slugs property.

     * protected static $slugs = [
     *      <column> => <slug_column>,
     * e.g.
     *      'title' => 'slug',
     * ];
     */
    public function initSlugTrait()
    {
        $slugs = isset(static::$slugs) ? static::$slugs : ['title' => 'slug'];

        $slugger = function ($entity) use ($slugs) {
            foreach ($slugs as $column => $slug_column) {
                //do nothing if the slug has been manually modified
                if (in_array($slug_column, $entity->getModifiedFields())) {
                    continue;
                }

                $entity->setRaw($slug_column, $this->slugify($entity->getRaw($column)));
            }
        };

        $this->addEventCallBack('insert', $slugger);
        $this->addEventCallBack('update', $slugger);
    }

    private function slugify($string)
    {
        return preg_replace('/[^a-z0-9]/', '-', strtolower($string));
    }
}
