<?php
namespace App\Http\Transformers;

use App\Tag;
use League\Fractal;

class TagTransformer extends Fractal\TransformerAbstract
{
	public function transform(Tag $tag)
	{
	    return [
	        'id'      => (int) $tag->id,
	        'name'   => $tag->name,

	    ];
	}
}