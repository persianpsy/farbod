<?php
/**
 * Written by Farbod
 */

namespace App\Fractal;



use Illuminate\Support\Collection;
use App\Transformers;


class Fractal
{
    private $data;
    private $transformer;

    public function __construct($data, Transformer $transformer)
    {
        $this->transformer = $transformer;
        $this->data        = $data;
    }

    /**
     * Transform Collection or model with Custom Transformer
     *
     *  @return array
     */
    public function transform()
    {
        $data = $this->data;

        if(is_object($data) && !$data instanceof Collection) {
            return $this->transformer->transform($data);
        }

        $data = $data instanceof Collection ? $data : collect($data);

        return $data->map(function ($transformable) {
            return $this->transformer->transform($transformable);
        });
    }

    public function transformAsPaginated()
    {
        try{
            $this->data->getCollection()->transform(function ($transformable) {
                return $this->transformer->transform($transformable);
            });
        }catch(\Exception $e) {
            throw new \Exception('we getting error with message "('.$e.')." Make sure the data be is a paginated collection!');
        }

        return $this->data;
    }
}
