<?php
namespace App\Http\Transformers;

use App\Quote;
use League\Fractal;

class QuoteTransformer extends Fractal\TransformerAbstract
{
	public function transform(Quote $quote)
	{
	    return [
	        'id'      => (int) $quote->id,
	        'title'   => $quote->title,
	        'author'   => $quote->author,

	    ];
	}
}