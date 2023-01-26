<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Option;
use App\Models\Question;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function created(Category $category)
    {
        $question = Question::Where('title', 'like', '%' . 'مشکل شما در چه دسته ای قرار دارد؟' . '%')->first();
        if ($question)
            Option::create([
                'value'=>$category->title,
                'en_value'=>$category->slug,
                'question_id'=>$question->id
            ]);
    }

    /**
     * Handle the Category "updated" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function updated(Category $category)
    {
        $option = Option::where('value',$category->getOriginal('title'))->first();
        if ($option)
            $option->update([
                'value'=>$category->title,
            ]);
    }

    /**
     * Handle the Category "deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        $option = Option::where('value',$category->getOriginal('title'))->first();
        if ($option)
            $option->delete();
    }

    /**
     * Handle the Category "restored" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function restored(Category $category)
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function forceDeleted(Category $category)
    {
        //
    }
}
