<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\Feed\Feed;
use Carbon\Carbon;
use Illuminate\Http\Request;


class FeedController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $messages = Feed::orderBy('id', 'desc')->paginate(20);

        return view('theme::feed.show')->with('messages',$messages);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message = Feed::find($id);

        if(!$message){
            abort(404);
        }

        $message->delete();

        return response('ok');

    }


}
