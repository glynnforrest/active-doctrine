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

        $this->addEventCallBack('insert', function ($entity) use ($slugs) {
            foreach ($slugs as $column => $slug_column) {
                //if slug has been modified, override
                if ($entity->has($slug_column)) {
                    continue;
                }

                $entity->setRaw($slug_column, $this->slugify($entity->getRaw($column)));
            }
        });
    }

    private function slugify($string)
    {
        return preg_replace('/[^a-z0-9]/', '-', strtolower($string));
    }
}
