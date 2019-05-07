<?php namespace App\Http\Controllers\Admin\Partner;
/**
 * @author: wanghui
 * @date: 2017/5/18 下午6:37
 * @email: hank.huiwang@gmail.com
 */
use App\Http\Controllers\Admin\AdminController;
use App\Models\PartnerOauth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OauthController extends AdminController
{

    /*新闻创建校验*/
    protected $validateRules = [
        'app_id'        => 'required',
        'description' => 'required|max:255',
        'status' => 'required|in:0,1,2',
        'tags' => 'required'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = PartnerOauth::query();

        if( isset($filter['app_id']) && $filter['app_id'] ){
            $query->where('app_id','like', '%'.$filter['app_id'].'%');
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }

        $oauthList = $query->orderBy('id','desc')->paginate(20);
        return view("admin.partner.oauth.index")->with('oauthList',$oauthList)->with('filter',$filter);
    }



    public function create()
    {
        return view("admin.partner.oauth.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->flash();

        $this->validateRules['app_id'] = 'required|unique:partner_oauth';

        $this->validate($request,$this->validateRules);

        $data = [
            'app_id'        => trim($request->input('app_id')),
            'app_secret'  =>Str::random(32),
            'product_id' => $request->input('tags'),
            'description'   => $request->input('description'),
            'status'       => $request->input('status',1),
        ];

        $version = PartnerOauth::create($data);

        if($version){
            $message = '创建成功';
            return $this->success(route('admin.partner.oauth.index'),$message);
        }

        return  $this->error("创建失败，请稍后再试",route('admin.partner.oauth.index'));

    }

    /**
     * 显示文字编辑页面
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,Request $request)
    {
        $oauth = PartnerOauth::find($id);

        if(!$oauth){
            abort(404);
        }

        return view("admin.partner.oauth.edit")->with(compact('oauth'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $oauth = PartnerOauth::find($id);
        if(!$oauth){
            abort(404);
        }

        $request->flash();
        if ($oauth->status == 1) {
            unset($this->validateRules['app_id']);
            unset($this->validateRules['tags']);
        }
        $this->validate($request,$this->validateRules);
        if ($oauth->status == 0) {
            $oauth->app_id = trim($request->input('app_id'));
            $oauth->product_id = $request->input('tags');
        }
        $oauth->status = trim($request->input('status'));
        $oauth->description = trim($request->input('description'));
        $oauth->api_url = trim($request->input('api_url'));

        $oauth->save();

        return $this->success(route('admin.partner.oauth.index'),"编辑成功");

    }

    /**
     * 删除
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        PartnerOauth::whereIn('id',$request->input('id'))->update(['status'=>0]);
        return $this->success(route('admin.partner.oauth.index'),'禁用成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        PartnerOauth::whereIn('id',$ids)->update(['status'=>1]);

        return $this->success(route('admin.appVersion.index'),'审核成功');

    }

}