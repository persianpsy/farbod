<?php
/**
 * Written by Farbod
 */

namespace App\Fractal;



trait Fraction
{
    public function simpleTransform($model, $transformer)
    {
        return fractal($model, new $transformer)->transform();
    }

    public function paginatedTransform($models, $transformer)
    {
        return $this->fractal($models, new $transformer)->transformAsPaginated();
    }
}
