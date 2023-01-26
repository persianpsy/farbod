<?php
/**
 * Written by Farbod
 */

namespace App\Fractal;



abstract class Transformer
{
    use Fraction;

    /**
     * @param $data
     *
     * @return array
     */
    public abstract function transform($data) :array;


}
