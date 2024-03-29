<?php
/**
 * 用户空间
 */
namespace App\Http\Controllers\Account;

use App\Models\Credit;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

class SpaceController extends Controller
{
    protected $user;

    protected function init(Request $request){
        $userId =  $request->route()->parameter('user_id',0);

        $user  = User::with('userData')->find($userId);

        if(!$user){
            abort(404);
        }
        $this->user = $user;
        View::share("userInfo",$user);
    }

    /**
     * 用户空间首页
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->init($request);
        $doings = $this->user->doings()->orderBy('created_at','DESC')->paginate(Config::get('api_data_page_size'));
        $doings->map(function($doing){
            $doing->action_text = Config::get('inwehub.user_actions.'.$doing->action);
        });
        $this->user->userData->increment('views');
        return view('theme::space.index')->with('doings',$doings);
    }

    /**
     * 用户提问
     * @return View
     */
    public function questions(Request $request)
    {
        $this->init($request);

        $questions = $this->user->questions()->orderBy('created_at','DESC')->paginate(Config::get('api_data_page_size'));
        return view('theme::space.questions')->with('questions',$questions);
    }

    /**
     * 用户回答
     * @return mixed
     */
    public function answers(Request $request)
    {
        $this->init($request);

        $answers = $this->user->answers()->with('question')->orderBy('created_at','DESC')->paginate(Config::get('api_data_page_size'));
        return view('theme::space.answers')->with('answers',$answers);
    }

    public function articles(Request $request)
    {
        $this->init($request);

        $articles = $this->user->articles()->orderBy('created_at','DESC')->paginate(Config::get('api_data_page_size'));
        return view('theme::space.articles')->with('articles',$articles);
    }


    /*我的金币*/
    public function coins(Request $request)
    {
        $this->init($request);

        $coins = Credit::where('user_id','=',$this->user->id)->where('coins','<>',0)->orderBy('created_at','DESC')->paginate(Config::get('api_data_page_size'));
        $coins->map(function($coin){
            $coin->actionText = Config::get('inwehub.user_actions.'.$coin->action);
        });
        return view('theme::space.coins')->with('coins',$coins);
    }


    /*我的经验*/
    public function credits(Request $request)
    {
        $this->init($request);

        $credits = Credit::where('user_id','=',$this->user->id)->where('credits','<>',0)->orderBy('created_at','DESC')->paginate(Config::get('api_data_page_size'));
        $credits->map(function($credit){
            $credit->actionText = Config::get('inwehub.user_actions.'.$credit->action);
        });
        return view('theme::space.credits')->with('credits',$credits);
    }


    /*我的粉丝*/
    public function followers(Request $request)
    {
        $this->init($request);

        $followers = $this->user->followers()->orderBy('attentions.created_at','asc')->paginate(Config::get('api_data_page_size'));
        return view('theme::space.followers')->with('followers',$followers);
    }


    /*我的关注*/
    public function attentions(Request $request)
    {
        $this->init($request);

        $source_type = $request->route()->parameter('source_type');
        $sourceClassMap = [
            'questions' => 'App\Models\Question',
            'users' => 'App\Models\User',
            'tags' => 'App\Models\Tag',
        ];

        if(!isset($sourceClassMap[$source_type])){
          abort(404);
        }

        $model = App::make($sourceClassMap[$source_type]);

        $attentions = $this->user->attentions()->where('source_type','=',$sourceClassMap[$source_type])->orderBy('attentions.created_at','desc')->paginate(Config::get('api_data_page_size'));
        $attentions->map(function($attention) use ($model) {
            $attention['info'] = $model::find($attention->source_id);
        });
        return view('theme::space.attentions')->with('attentions',$attentions)->with('source_type',$source_type);

    }

    public function collections(Request $request)
    {
        $this->init($request);

        $source_type = $request->route()->parameter('source_type');

        $sourceClassMap = [
            'questions' => 'App\Models\Question',
            'articles' => 'App\Models\Article',
        ];

        if(!isset($sourceClassMap[$source_type])){
            abort(404);
        }

        $model = App::make($sourceClassMap[$source_type]);

        $collections = $this->user->collections()->where('source_type','=',$sourceClassMap[$source_type])->orderBy('collections.created_at','desc')->paginate(Config::get('api_data_page_size'));
        $collections->map(function($collection) use ($model) {
            $collection['info'] = $model::find($collection->source_id);
        });

        return view('theme::space.collections')->with('collections',$collections)->with('source_type',$source_type);


    }





}
