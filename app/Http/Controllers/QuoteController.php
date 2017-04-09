<?php

namespace App\Http\Controllers;
ini_set('max_execution_time', 3000);
use Illuminate\Http\Request;
use App\Quote;
use App\Tag;
use DB;
use App\Http\Transformers\TagTransformer;
use App\Http\Transformers\QuoteTransformer;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use EllipseSynergie\ApiResponse\Contracts\Response;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(Response $response)
    {
        $this->response = $response;
    }
    public function index()
    {
        //
    }

    public function find_quotes()
    {
        //
        $quotes = Quote::all();

        // Return a collection of $books
        return $this->response->withCollection($quotes, new QuoteTransformer);
    }

    public function find_tags()
    {
        //
        $tags = Tag::all();

        return $this->response->withCollection($tags, new TagTransformer);
    }

    public function find_quotes_by_tag($name)
    {
        //
        $tag=Tag::where('name',$name)->first();

        //$quotes=Quote::all();
        $quote_tags=DB::table('quote_tag')->where('tag_id', $tag->id)->get();

        $quotes=array();
        $i=0;
        foreach ($quote_tags as $quote_tag) {
            # code...
            $quotes[$i]=Quote::where('id',$quote_tag->quote_id)->first();
            $i++;
        }

        return $this->response->withCollection($quotes, new QuoteTransformer);
    }



    public function quotographed_crawler()
    {

        $client = new Client();

        $guzzleClient = new GuzzleClient(array(
            'timeout' => 600,
        ));
        $client->setClient($guzzleClient);

        $page_number=1;
        $check_empty=true;
        
         while($check_empty) {
             $craw = $client->request('GET', 'http://www.quotographed.com/page/'.$page_number);                
                
                $page_title=$craw->filter('title')->each(function($node){
                         return $node->text();
                     });

                $i= strcmp( $page_title[0],"Page not found - Quotographed");
                if ($i==0) {
                     # code...
                     return 'ok, data crawled';
                }
                

                $articles=$craw->filter('article');
                if(is_null($articles))
                    $check_empty=false;
                else{


                    foreach ($articles as $article) { 
                    # code...
                        $article=new Crawler($article);
                        $h2=$article->filter('h2');
                        $t=$h2->filter('a')->each(function($node){
                            return $node->text();
                        });
                        
                        $title=$t[0];

                        $title = trim($title,'"');

                        $quote=Quote::where('title',$title)->first();
                        if (is_null($quote)){
                            $quote=new Quote();
                            $quote->title=$title;
                            $quote->save();
                
                            $quote=Quote::where('title',$title)->first();
                    
                            $ul=$article->filter('.post-categories');
                            $lists=$ul->filter('a');
                            $lists=$ul->filter('a')->each(function($node){
                                return $node->text();
                            });

                            foreach ($lists as $list) {
                            # code...
                        
                                $tag=Tag::where('name',$list)->first();
                          
                                if (is_null($tag)) {
                                # code...
                                    $tag=new Tag();
                                    $tag->name=$list;
                                    $tag->save();
                                    $tag=Tag::where('name',$list)->first();
                                }

                                DB::table('quote_tag')->insert(
                                    ['quote_id' => $quote->id, 'tag_id' =>$tag->id]
                                );
                            }

                        }
                    }

                
                }
            $page_number++;
            } 
        
        $titl='ok';
        return $titl;
        //echo $pa;
     
    }


    public function brainyquote_crawler()
    {
        

         $client = new Client();

 // Go to the brainyquote.com website
         $craw = $client->request('GET', 'https://www.brainyquote.com/quotes/topics.html');


         $goutteClient = new Client();
         $guzzleClient = new GuzzleClient(array(
             'timeout' => 60,
         ));
         $goutteClient->setClient($guzzleClient);


//find topics    
        $craw=$craw->filter('.content');    
        $craw=$craw->filter('.bqLn');

         // Get the latest post in this category and display the titles
        $subjects=$craw->filter('a')->each(function ($node) {
           return $node->text();
        });
        
        foreach ($subjects as $subject) {
            # code...
            $tag1=Tag::where('name',$subject)->first();
                        
            if (is_null($tag1)) {
                # code...
                $tag1=new Tag();
                $tag1->name=$subject;
                $tag1->save();
                $tag1=Tag::where('name',$subject)->first();
            }

            //https://www.brainyquote.com/quotes/topics/topic_happiness.html
            $subjectName=strtolower($tag1->name);
            
            $quoteCrawlers = $client->request('GET', 'https://www.brainyquote.com/quotes/topics/topic_'.$subjectName.'.html');
            $quoteCrawlers=$quoteCrawlers->filter('.m-brick');

            foreach ($quoteCrawlers as $quoteCrawler) {
                # code...
                $quoteCrawler=new Crawler($quoteCrawler);

                $quot=$quoteCrawler->filter('.b-qt')->each(function ($node) {
                   return $node->text();
                });
                if (isset($quot[0])) {
                    # code...
                
                    $title=$quot[0];
                    $quote=Quote::where('title',$title)->first();
                    if(is_null($quote)){
                        $quote=new Quote();
                        $quote->title=$title;
                    
                        $author=$quoteCrawler->filter('.bq-aut')->each(function ($node) {
                            return $node->text();
                        });
                        if (isset($author[0])) {
                            # code...
                            $quote->author=$author[0];
                        }
                        $quote->save();
                        $quote=Quote::where('title',$title)->first();
                    
                        $t=$quoteCrawler->filter('.kw-box');
                        $lists=$t->filter('a')->each(function ($node) {
                            return $node->text();
                        });
                    
                        foreach ($lists as $list) {
                            # code...
                        
                            $tag=Tag::where('name',$list)->first();
                          
                            if (is_null($tag)) {
                            # code...
                                $tag=new Tag();
                                $tag->name=$list;
                                $tag->save();
                                $tag=Tag::where('name',$list)->first();
                            }

                            DB::table('quote_tag')->insert(
                                ['quote_id' => $quote->id, 'tag_id' =>$tag->id]
                            );
                        }

                        DB::table('quote_tag')->insert(
                            ['quote_id' => $quote->id, 'tag_id' =>$tag1->id]
                        );
                    }

                }                

            }

        }
        return 'ok';
        
    }


    public function quotationspage_crawler()
    {

        $client = new Client();

// Go to the symfony.com website
        $craw = $client->request('GET', 'http://www.quotationspage.com/subjects');


        $goutteClient = new Client();
        $guzzleClient = new GuzzleClient(array(
            'timeout' => 60,
        ));
        $goutteClient->setClient($guzzleClient);


        
        $craw=$craw->filter('.subjects');

        // Get the latest post in this category and display the titles
        $subjects=$craw->filter('a')->each(function ($node) {
            return $node->text();
        });
        foreach ($subjects as $subject) {
            # code...
            $i= strcmp( $subject,"Search");
            if ($i==0) {
                 # code...
                return 'ok, data crawled';
            }
            $tag=Tag::where('name',$subject)->first();
                        
            if (is_null($tag)) {
                # code...
                $tag=new Tag();
                $tag->name=$subject;
                $tag->save();
                $tag=Tag::where('name',$subject)->first();
            }

            $craw = $client->request('GET', 'http://www.quotationspage.com/subjects'.$subject);
            $craw=$craw->filter('.quote');
                       


        }
        return $cs;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
