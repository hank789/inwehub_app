<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class AdminController extends Controller {




    public function __construct(){


        /*未审核问题数*/
        $notVerifiedData['questions'] = Question::where('status','=',0)->count();
        /*未审核回答数*/
        $notVerifiedData['answers'] = Answer::where('status','=',0)->count();
        /*未审核文章数*/
        $notVerifiedData['articles'] = Article::where('status','=',0)->count();
        /*未审核评论数*/
        $notVerifiedData['comments'] = Comment::where('status','=',0)->count();

        View::share('notVerifiedData',$notVerifiedData);


    }





}
