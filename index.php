<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>Pinst 在线聊天</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--[if lt IE 9]>
    <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
html{
    height: 100%;
}
        html {
    font-family: sans-serif;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        body {
    margin: 0;
}
        article,
        aside,
        details,
        figcaption,
        figure,
        footer,
        header,
        hgroup,
        main,
        menu,
        nav,
        section,
        summary {
    display: block;
}
        audio,
        canvas,
        progress,
        video {
    display: inline-block;
    vertical-align: baseline;
        }
        audio:not([controls]) {
display: none;
            height: 0;
        }
        [hidden],
        template {
    display: none;
}
        a {
    background-color: transparent;
        }
        a:active,
        a:hover {
    outline: 0;
}
        abbr[title] {
    border-bottom: 1px dotted;
        }
        b,
        strong {
    font-weight: bold;
        }
        dfn {
    font-style: italic;
        }
        h1 {
    margin: .67em 0;
            font-size: 2em;
        }
        mark {
    color: #000;
    background: #ff0;
}
        small {
    font-size: 80%;
        }
        sub,
        sup {
    position: relative;
    font-size: 75%;
            line-height: 0;
            vertical-align: baseline;
        }
        sup {
    top: -.5em;
        }
        sub {
    bottom: -.25em;
        }
        img {
    border: 0;
}
        svg:not(:root) {
    overflow: hidden;
}
        figure {
    margin: 1em 40px;
        }
        hr {
    height: 0;
    -webkit-box-sizing: content-box;
            -moz-box-sizing: content-box;
            box-sizing: content-box;
        }
        pre {
    overflow: auto;
}
        code,
        kbd,
        pre,
        samp {
    font-family: monospace, monospace;
            font-size: 1em;
        }
        button,
        input,
        optgroup,
        select,
        textarea {
    margin: 0;
    font: inherit;
    color: inherit;
}
        button {
    overflow: visible;
}
        button,
        select {
    text-transform: none;
        }
        button,
        html input[type="button"],
        input[type="reset"],
        input[type="submit"] {
    -webkit-appearance: button;
            cursor: pointer;
        }
        button[disabled],
        html input[disabled] {
    cursor: default;
}
        button::-moz-focus-inner,
            input::-moz-focus-inner {
            padding: 0;
            border: 0;
        }
        input {
    line-height: normal;
        }
        input[type="checkbox"],
        input[type="radio"] {
    -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            padding: 0;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
    height: auto;
}
        input[type="search"] {
    -webkit-box-sizing: content-box;
            -moz-box-sizing: content-box;
            box-sizing: content-box;
            -webkit-appearance: textfield;
        }
        input[type="search"]::-webkit-search-cancel-button,
        input[type="search"]::-webkit-search-decoration {
    -webkit-appearance: none;
        }
        fieldset {
    padding: .35em .625em .75em;
            margin: 0 2px;
            border: 1px solid #c0c0c0;
        }
        legend {
    padding: 0;
    border: 0;
}
        textarea {
    overflow: auto;
}
        optgroup {
    font-weight: bold;
        }
        table {
    border-spacing: 0;
            border-collapse: collapse;
        }
        td,
        th {
    padding: 0;
}
        /*! Source: https://github.com/h5bp/html5-boilerplate/blob/master/src/css/main.css */
        @media print {
    *,
    *:before,
            *:after {
        color: #000 !important;
        text-shadow: none !important;
                background: transparent !important;
                -webkit-box-shadow: none !important;
                box-shadow: none !important;
            }
            a,
            a:visited {
        text-decoration: underline;
            }
            a[href]:after {
        content: " (" attr(href) ")";
            }
            abbr[title]:after {
        content: " (" attr(title) ")";
            }
            a[href^="#"]:after,
            a[href^="javascript:"]:after {
        content: "";
    }
            pre,
            blockquote {
        border: 1px solid #999;

                page-break-inside: avoid;
            }
            thead {
        display: table-header-group;
    }
            tr,
            img {
        page-break-inside: avoid;
            }
            img {
        max-width: 100% !important;
            }
            p,
            h2,
            h3 {
        orphans: 3;
        widows: 3;
    }
            h2,
            h3 {
        page-break-after: avoid;
            }
            .navbar {
        display: none;
    }
            .btn > .caret,
            .dropup > .btn > .caret {
        border-top-color: #000 !important;
            }
            .label {
        border: 1px solid #000;
            }
            .table {
        border-collapse: collapse !important;
            }
            .table td,
            .table th {
        background-color: #fff !important;
            }
            .table-bordered th,
            .table-bordered td {
        border: 1px solid #ddd !important;
            }
        }
        body{
    padding: 0;
    margin: 0;
    height: 100%;
    background: url("http://static.aoyel.com/assets/images/pinst.png");
    left: 0px;
            z-index: 999;
            font-size: 1em;
            background-attachment: fixed;
            background-size:cover;
        }

        a{
    text-decoration: none;
        }

        .wrap-container{
    display: block;
    position: relative;
    width: 96%;
    max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .sender-panel{
    display: none;
    width: 100%;
    padding: 1em 0;;
            position: fixed;
            bottom: 0;
            left: 0;
        }
        .login-panel{
    display: none;
    text-align: center;
            padding-top: 2em;
        }

        .login-panel img {
    width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 3em;
        }

        .form-control{
    display: block;
    width: 100%;
    height: 34px;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            -webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
        }

        textarea.form-control,
        input.form-control{
    border-radius: 0;
            box-shadow: none;
            background: none;
            color: white;
            max-width: 100%;
        }

        input.form-control{
    width: 80%;
    max-width: 350px;
            display: inline-block;
        }

        textarea.form-control:focus{
    box-shadow: none;
        }

        .msg-item{
    width: 100%;
    padding: 1em 0;
            position: relative;
        }

        .msg-item a{
    display: inline-block;
    vertical-align: top;
        }

        .msg-item em{
    display: block;
    text-align: center;
            left: 0;
            position: relative;
            color: white;
            font-size: 12px;
            font-style: normal;
        }

        .msg-item::before{
    display: block;
    clear: both;
}

        .msg-item::after{
    display: block;
    clear: both;
}

        .msg-item span{
    display: inline-block;
    padding: 1em .8em;
            max-width: 60%;
            color: white;
            background: rgba(0,0,0,.6);
        }
        .msg-item.system{
    text-align: center;
        }

        .msg-item.system span{
    display: inline;
    padding: .3em .6em;
            border-radius: 3px;
            font-size: 12px;
            -webkit-box-shadow: 0 0 3px rgba(0,0,0,.06);
            -moz-box-shadow: 0 0 3px rgba(0,0,0,.06);
            box-shadow: 0 0 3px rgba(0,0,0,.06);
        }

        .msg-item.right{
    text-align: right;
        }

        .msg-item img.avatar{
    width: 40px;
            height:40px;
            margin:0 .5em;
            vertical-align: top;
            border-radius: 50%;
        }

        .msg-item.right a{
    float: right;
}

        .retry-panel{
    display: none;
    padding-top: 5em;
            text-align: center;
        }

        .retry-panel .btn{
    width: 80%;
    color: #fff;
    padding: .8em 0;
            display: inline-block;
            border: 1px solid #eee;
            background: none;
        }

        .retry-panel .btn:hover{
    background: rgba(255,255,255,.1);
}

        @media (min-width: 769px) {
    ::-webkit-scrollbar {
        width:9px;
                height:9px;
                box-sizing:border-box;
                background:#eee
            }
            ::-webkit-scrollbar-button {
        width:9px;
                height:12px;
                background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAAUCAYAAADRA14pAAADr0lEQVRYR71Yy04iQRQtE10IRiSBOLbOUvZmfkIlLHXjI+jCDzAm8w8TJKxZyENdqEui8BPuDVtsHCNGQcFEWUzdSt/KtbqqqZ44U0kn1V2n69xz63W6x5h9iXFoNADe521dftnibJlt+7PCjdmycpzz9vbmmvCTk5PzvK0NuNvbWyNuYWEBcbbUX8obSvBgMDAKiUQiUrDLi0nNPC9eYqwFfyWvTvAPHsm1JhqHEl9dXbHV1VUJo4Lv7u6k4JOTE7a5uSlxc3Nz/0ww5VXjR15VMIjFoop2+v2+EAJisaDoaDQqR/j+/l7gjo+PJW5ra0vUZ2dnqWDko1zqM+fi4kL0RxOMMaytrUneXC4ncMhFYzg4OBA4KpiK1Yl2Xl9fXSqWip6ampLEDw8PbrVa9U2S7e1tlkwmdYIBC6J1CXfOz8/ljAHRNIbFxcXs0tJShb/rHB4eShxw0RgymczPVCr1CwWrRCOJeQesVqtJUevr61JwPp+XxHt7e6xYLErc/v6+OqVHJvrl5cWt1+u+BEIMNzc3UvDj46NbqYD2zwViaLfbWsGBU+vs7EwIASIow+GQYSA8e5K4UCgIHBBBeX9/Z+VyWdT5CAliJabApdTr9UR/VDTGQPeO5+dngUMuGgPiQu3S3W7XHR8fl2IxaHjWbDalYAhwYmJCNH98fEht8KzVaukEA8a4WQIvdgKiacJjsZicWZgYwJZKJZlwiGF6etq3hpWk+24dzKAOODMzI4lhrZs6I2t9FB+2+3ghcZjIIF4YCJiFUJA31AjbCrY8N/9aMH2RCrbhDSP4OydKBETZ4W09fn3jV8SAG/Dnv/kFFtS22PC2eGdWOJNg3fnos3iXl5ci6HQ6zTxryVRbeXp6KjAbGxvMYCt1XDQZgpdy0UbV0lI+ikNuk9NCLN21fU4LQXA2ersgbP+fXBZiwG05jqNzWUE7NLwueHVmBxrpLg3c4OwoJ9aR+6udFgvhsugA/DeHZ3JaWi+tOi1q9bxdkHU6nU8uCxwPlkQiEeSjTaKFw8M+1JGmDg+4EUddFsSA3KFGWGcAVlZWBId3zrGnpyff4Z/NZgUmHo+bBIc2Hgqv+Cy14Q61hlUDgNkEcs8AMPXwR8zOzo48/Olc5vWRaxh4qctCsdCPajzAcFBOrI8yHtpdWj2HG42G6G95eZl55yHDLyokOjo6EtXd3V1Gvqh061e3jAAnjAfloi/Tcxi4KR/FIXeYc9jmFwpw2PwGUgY58NaG1/rX0h9d1DUzJEP0JgAAAABJRU5ErkJggg==) no-repeat
            }
            ::-webkit-scrollbar-button:vertical:start {
        background-position:0 0
            }
            ::-webkit-scrollbar-button:vertical:start:hover {
        background-position:-10px 0
            }
            ::-webkit-scrollbar-button:vertical:start:active {
        background-position:-20px 0
            }
            ::-webkit-scrollbar-button:vertical:end {
        background-position:-30px 0
            }
            ::-webkit-scrollbar-button:vertical:end:hover {
        background-position:-40px 0
            }
            ::-webkit-scrollbar-button:vertical:end:active {
        background-position:-50px 0
            }
            ::-webkit-scrollbar-button:horizontal:start {
        background-position:0 -11px
            }
            ::-webkit-scrollbar-button:horizontal:start:hover {
        background-position:-10px -11px
            }
            ::-webkit-scrollbar-button:horizontal:start:active {
        background-position:-19px -11px
            }
            ::-webkit-scrollbar-button:horizontal:end {
        background-position:-30px -11px
            }
            ::-webkit-scrollbar-button:horizontal:end:hover {
        background-position:-40px -11px
            }
            ::-webkit-scrollbar-button:horizontal:end:active {
        background-position:-50px -11px
            }
            ::-webkit-scrollbar-track-piece {
        background-color:rgba(0,0,0,.15);
                -webkit-border-radius:5px
            }
            ::-webkit-scrollbar-thumb {
        background-color:#E7E7E7;
                border:1px solid rgba(0,0,0,.21);
                -webkit-border-radius:5px
            }
            ::-webkit-scrollbar-thumb:hover {
        background-color:#F6F6F6;
                border:1px solid rgba(0,0,0,.21)
            }
            ::-webkit-scrollbar-thumb:active {
        background:-webkit-gradient(linear,left top,left bottom,from(#E4E4E4),to(#F4F4F4))
            }
            ::-webkit-scrollbar-corner {
        background-color:#f1f1f1;
                -webkit-border-radius:1px
            }
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="chat-panel">
        <div class="wrap-container">
        </div>
    </div>
    <section class="retry-panel">
        <a href="javascript:;" class="btn">
重新链接
        </a>
    </section>
    <section class="login-panel">
        <img src="http://static.aoyel.com/images/avatar.png"><br>
        <input placeholder="请输入您的昵称" class="form-control" type="text">
    </section>

    <section class="sender-panel">
        <div class="wrap-container">
            <textarea placeholder="请输入您要发送的消息" name="content" class="form-control"></textarea>
            <div class="clearfix">
                <a href="javascript:;" style="display: none" class="btn btn-default btn-send pull-right">发送</a>
            </div>
        </div>
    </section>
    </div>
<script src="http://cdn.bootcss.com/jquery/2.1.0/jquery.min.js"></script>
<script src="http://192.168.1.109:8080/target/target-script-min.js#anonymous"></script>

<script>
    function socket(){
        this.init = function(url,_debug){
            this.isopen = false;
            this.debug = _debug || true;
            this.ws = new WebSocket(url);
            this.bindEvent();
            this.eventMap = [];
            return this;
        }

        this.isOpen = function(){
            var _this = this;
            return _this.isopen;
        }

        this.send = function(data,callback){
            var _this = this;
            if(!_this.isopen){
                _this.log("socket is closed,can't send message!");
                return false;
            }
            _this.ws.send(data);
            callback && callback();
        }

        this.log = function(msg){
            if(this.debug){
                console.log(msg);
            }
        }

        this.on = function(event,callback){
            var b = null;
            if(this.eventMap.hasOwnProperty(event)){
                b = this.eventMap[event];
            }else{
                b = new Array();
            }
            b.push(callback);
            this.eventMap[event] = b;
        }

        this.toggle = function(event,param){
            var cs = null;
            if(this.eventMap.hasOwnProperty(event)){
                cs = this.eventMap[event];
            }
            if(!cs){
                return false;
            }
            for(c in cs){
                cs[c] && cs[c](param);
            }
            return true;
        }

        this.bindEvent = function(){
            var _this = this;
            _this.ws.onopen = function(event){

                _this.log(event);
                if(event.type == "open"){
                    _this.isopen = true;
                }
                _this.toggle("open",event);
            }
            _this.ws.onerror = function(event){
                _this.log(event);
                _this.toggle("error",event);
            }
            _this.ws.onmessage = function(event){
                _this.log(event);
                _this.toggle("message",event);
            }
            _this.ws.onclose = function(event){
                _this.log(event);
                _this.isopen = false;
                _this.toggle("close",event);
            }
        }
    }
    $(function(){

        var url = "ws://192.168.1.109:3927";
        var isLogin = false;
        var panel = $(".chat-panel .wrap-container");
        var btnSend = $(".btn-send");
        var senderPanel = $(".sender-panel");
        var loginPanel = $(".login-panel");
        var retryPanel = $(".retry-panel");
        var msgContent = $("textarea[name='content']");

        var s = new socket();

        var renderMsg = function(msg,type,target){
            var tpl = $('<div class="msg-item"><a href="javascript:;"><img class="avatar" src="http://static.aoyel.com/images/avatar.png"><em role="name"></em></a><span></span></div>');
            if(type == "system"){
                tpl.addClass("system");
                tpl.find("a").remove();
            }else if(type == "self"){
                tpl.addClass("right");
            }
            if(target){
                tpl.find("em[role='name']").text(target);
            }
            tpl.find("span").html(msg);
            panel.append(tpl);
            $('html, body').animate({scrollTop: $(document).height()},400);
        }

        var buildMsg = function(data,action,target){
            var param = {};
            param['action'] = action || "msg";
            param['target'] = target || "";
            param['data'] = data;
            return JSON.stringify(param);
        }

        try {
            s.init(url,true);
        }catch (e){
            console.log(e);
        }


        $("input",loginPanel).on("keydown",function(event){
            if(event.keyCode == 13){
                var name = $(this).val();
                if(name != ""){
                    s.send(buildMsg(name,"login"));
                }else{
                    $(this).focus();
                }
            }
        })

        msgContent.on("keydown",function(event){
            if(event.keyCode == 13){
                btnSend.click();
                event.preventDefault();
            }
        })

        retryPanel.on("click","a",function(){
            s.init(url,false);
        })

        btnSend.on("click",function(e){
            var content = msgContent.val();
            s.send(buildMsg(content,"message"),function(){
                renderMsg(content,"self");
                msgContent.val("");
            });

        });

        s.on("message",function(data){
            processMessage(data.data);
        })

        var processMessage = function(data){
            data = jQuery.parseJSON(data);
            if(data.action == "login"){
                if(data.data == "ok"){
                    loginPanel.fadeOut();
                    senderPanel.show();
                    panel.css("padding-bottom",senderPanel.height()+10);
                }
            }else if(data.action == "message"){
                renderMsg(data.data,null,data.target);
            }else if(data.action == "notify"){
                renderMsg(data.data,"system");
            }
        }

        s.on("open",function(data){
            if(data.type == "open"){
                renderMsg("连接服务器成功","system");
            }
            panel.show();
            retryPanel.hide();
            loginPanel.show();
        })

        s.on("close",function(data){
            renderMsg("服务器断开了","system");
            panel.hide();
            retryPanel.show();
            senderPanel.hide();
        })
    })

    </script>
</body>
</html>