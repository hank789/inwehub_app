/**
 * Created by simon on 2015/4/22.
 */
/*ajax设置项*/

$(function () {
    /*ajax设置项*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#sliderbar_control").click(function(){
        var sidebar_collapse = 0;
        if($('body').hasClass('sidebar-collapse')){
            sidebar_collapse = 1;
        }
        $.get(site_url+'/admin/index/sidebar?collapse='+sidebar_collapse,function(msg){
            console.log(msg);
        });
    });

    //Enable iCheck plugin for checkboxes
    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    });


    //全部选中checkbox
    $('.checkbox-toggle').on('ifChecked', function(event){
        $("input[type='checkbox'][class!='checkbox-toggle']").iCheck('check');
    });

    //全部取消选中checkbox
    $('.checkbox-toggle').on('ifUnchecked', function(event){
        $("input[type='checkbox'][class!='checkbox-toggle']").iCheck('uncheck');
    });


    /*daterange控件*/
    $('#date_range').daterangepicker({
        format: 'YYYY-MM-DD',
        locale: {
            applyLabel: '确认',
            cancelLabel: '取消',
            fromLabel: '从',
            toLabel: '到',
            weekLabel: '星期',
            customRangeLabel: '自定义范围',
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.monthsShort(),
            firstDay: moment.localeData()._week.dow
        }
    });

});



/**
 * 编辑器图片图片文件方式上传
 * @param file
 * @param editor
 * @param welEditable
 */
function upload_editor_image(file,editorId){
    data = new FormData();
    data.append("file", file);
    $.ajax({
        data: data,
        type: "POST",
        dataType : 'text',
        url: "/manager/image/upload",
        cache: false,
        contentType: false,
        processData: false,
        success: function(url) {
            $('#'+editorId).summernote('insertImage', url, function ($image) {
                $image.css('width', $image.width() / 2);
                $image.addClass('img-responsive');
            });
        }
    });
}




/*删除确认*/
function confirm_delete(message){
    if(!confirm(message)){
        return false;
    }
    $("#item_form").submit();
}


/*确认提交表单*/
function confirm_submit(form_id,action_url,message){
    if(!confirm(message)){
        return false;
    }
    $("#"+form_id).attr("action",action_url);
    $("#"+form_id).submit();
}

/**
 * 设置当前页面高亮的菜单
 * @param $parentid
 * @param $url
 */
function set_active_menu(parent_id,url){

    $("#"+parent_id+">li>a[href='"+url+"']",0).parent().addClass("active");
    $("#"+parent_id).parent().addClass("active");

}

jQuery.DuoImgsYulan = function(file, id, length, fileId) {
    for (var i = 0; i < length; i++) {
        if (!/image\/\w+/.test(file[i].type)) {
            alert("请选择图片文件");
            return false;
        }
        if (file[i].size > 2048 * 1024) {
            alert("图片不能大于2MB");
            continue;
        }
        var img;
        console.log(file[i]);
        console.log("file-size=" + file[i].size);
        var reader = new FileReader();
        reader.onloadstart = function(e) {
            console.log("开始读取....");
        }
        reader.onprogress = function(e) {
            console.log("正在读取中....");
        }
        reader.onabort = function(e) {
            console.log("中断读取....");
        }
        reader.onerror = function(e) {
            console.log("读取异常....");
        }
        reader.onload = function(e) {
            console.log("成功读取....");
            var div = document.createElement("div"); //外层 div
            div.setAttribute("style", "position:relative;width:inherit;height:inherit;float:left;z-index:2;width:300px;margin-left:8px;margin-right:8px;");
            var del = document.createElement("div"); //删除按钮div
            del.setAttribute("style", "position: absolute; bottom: 4px; right: 0px; z-index: 99; width: 30px; height:30px;border-radius:50%;")
            var delicon = document.createElement("img");
            delicon.setAttribute("src", "https://cdn.inwehub.com/system/deleted.png");
            delicon.setAttribute("title", "删除");
            delicon.setAttribute("style", "cursor:pointer;width: 30px; height:30px");
            del.onclick = function() {
                this.parentNode.parentNode.removeChild(this.parentElement);
                ClearfirtsImg(fileId);
            };
            del.appendChild(delicon);
            div.appendChild(del);
            var imgs = document.createElement("img"); //上传的图片
            imgs.setAttribute("name", "loadimgs");
            imgs.setAttribute("src", e.target.result);
            imgs.setAttribute("width", 300);
            if (document.getElementById(id).childNodes.length > length-1) {
                document.getElementById(id).removeChild(document.getElementById(id).firstChild);
            }
            div.appendChild(imgs)
            document.getElementById(id).appendChild(div);
        }
        reader.readAsDataURL(file[i]);
    }
}

function uploadAndPreviewImg(length, fileId, reviewId) {
    $.DuoImgsYulan(document.getElementById(fileId).files, reviewId, length, fileId);
}

function ClearfirtsImg(fileId) {
    var file = $("#"+fileId)
    file.after(file.clone().val(""));
    file.remove();
}