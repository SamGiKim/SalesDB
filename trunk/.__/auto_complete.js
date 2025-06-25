AutoComplete.send_xhr = function($qs, callback) {
    var _URL = "./sql_relay.php";
    var xhr = new XMLHttpRequest();
    xhr.open("GET", `${_URL}?${$qs}`, true);
    xhr.send();
    xhr.onreadystatechange = function(ev) {
        if(xhr.status == 200) {
            if(xhr.responseText == "") return;
            var json = JSON.parse(xhr.responseText);
            callback(json);
        }
    }
}

AutoComplete.obj2qs = function(obj) {
    return Object.keys(obj).map(function(key) {
        return key + '=' + obj[key]
    }).join('&');
}

AutoComplete.touch_list = function(e, input_id, ac_id) {
    // 터치시 목록 반영
    var input_el = document.querySelector(input_id);
    input_el.value = e.target.innerHTML;
    // 목록 닫기
    var ac_el = document.querySelector(ac_id);
    ac_el.style.visibility = "hidden";

}

function AutoComplete($target, $cmd, $src = null) {

    this.init = function() {
        $target.setAttribute("autocomplete", "off");
        $target.setAttribute("placeholder", "룩업");
        this.$target = $target; // 자동완성 대상
        this.$id = `autocomplete__${Math.random().toString(16).slice(2)}`;
        if("SVCBD*".indexOf($cmd.type) < 0) {
            throw new Error("Type is not exist.");
        }
        /* ----------------------------------- 커맨드 ---------------------------------- */
        this.$cmd = {
            cmd: $cmd.cmd,
            type : $cmd.type,
            val1 : "", // 초기화 시점에 val1 이고
        };
        /* ----------------------------------- 커맨드 ---------------------------------- */
        this.$delay = 50;  
        this.$selected_item = -1;
        this.$list = [];
        this.$xhr_queue = [];
        this.$src = $src;
    }

    this.render = function() {
        var wrapper = document.createElement("div");
        wrapper.innerHTML = this.template();
        document.body.append(wrapper);
    }

    this.template = function() {
        var _BD_COLOR = "#BDCDD6";
        var _BG_COLOR = "#F8F6F4";
        var pos = this.$target.getBoundingClientRect();
        var target_h = this.$target.offsetHeight;
        var style = `position:absolute; top:${pos.top+target_h}px; left:${pos.left}px;
                    width:${pos.width}px; font-size: 12px; z-index: 100;
                    border: 1px solid ${_BD_COLOR}; background-color: ${_BG_COLOR};`;
            style += "visibility: hidden;";
        var list_style = `list-style-type: none; margin: 10px 0px; padding: 0px 10px;
                    cursor: pointer; overflow-y: auto;`;
        var html = `
        <div id="${this.$id}" class="auto_complete_wrapper" style="${style}">
            <ul class="auto_complete__list" style="${list_style}">
            </ul>
        </div>`;
        return html;
    }

    this.clear_xhr_queue = function() {
        for(var i = 0; i < this.$xhr_queue.length; i += 1) {
            clearTimeout(this.$xhr_queue[i]);
        }
        this.$xhr_queue = [];
    }

    this.keydown_handler = function(e, self) {
        switch(e.keyCode) {
            case 9:  // TAB
            self.close_list();
            break;
            case 13: // Enter => SUMIT 금지
            if(!self.is_closed()) { // 닫혀있지 않을 때만 금지
                e.preventDefault();    
            }
            break;
            case 27: // ESC
            if(self.is_closed()) {
                try {
                    redirectToSalesMain(); // 모달창 닫기
                } catch (error) {}
            }
            break;
            case 38: // UP
            case 40: // DOWN
            e.preventDefault();
            break;
        }
    }

    this.keyup_handler = function(e, self) {
        var _IS_HANGUEL = (229 == e.keyCode);                   // 한글
        var _IS_AZ09    = ( (48 <= e.keyCode && e.keyCode <= 57)
        || (64 < e.keyCode && e.keyCode < 123));  // [a-z][A-Z][0-9]
        switch(self.$cmd.type) {
            case 'S':
            if(_IS_AZ09) {
                if(e.target.value == "") return;
                self.$cmd.val1 = e.target.value; // val1 갱신됨
                
                self.clear_xhr_queue();
                var _query_str = AutoComplete.obj2qs(self.$cmd);
                var ticket = setTimeout(function() {
                    AutoComplete.send_xhr(_query_str, function(json) {
                        self.refresh_list(json);
                    });
                }, self.$delay);
                self.$xhr_queue.push(ticket);
            }
            break;
            default:
            if(_IS_HANGUEL || _IS_AZ09) {
                if(e.target.value == "") return;
                self.$cmd.val1 = e.target.value; // val1 갱신됨
                
                self.clear_xhr_queue();
                var _query_str = AutoComplete.obj2qs(self.$cmd);
                var ticket = setTimeout(function() {
                    AutoComplete.send_xhr(_query_str, function(json) {
                        self.refresh_list(json);
                    });
                }, self.$delay);
                self.$xhr_queue.push(ticket);
            }
            break;
        }
        
        // console.log(e.keyCode);
        switch(e.keyCode) {
            case 13: // Enter
            self.$target.value = self.$list[self.$selected_item].name;
            if(self.$src != null) self.$src.value = self.$list[self.$selected_item].id;
            self.close_list();
            break;
            
            case 27: // ESC
            self.close_list();
            break;
            case 38: // UP
            self.select_prev();
            break;
            case 40: // DOWN
            self.open_list();
            self.select_next();
            break;
        }
    }

    this.bind = function() {
        var self = this;
        this.$target.addEventListener("keydown", function(e) { self.keydown_handler(e, self); });
        this.$target.addEventListener("keyup", function(e) { self.keyup_handler(e, self); });
    }

    // ---------------- LIST VIEW --------------------

    this.refresh_list = function(arr) {
        var wrapper_el = document.querySelector(`#${this.$id}`);
        if(arr.length == 0) {
            wrapper_el.style.visibility = "hidden";
        } else {
            wrapper_el.style.visibility = "unset";
            var list_el = document.querySelector(`#${this.$id}>.auto_complete__list`);
            list_el.innerHTML = `${arr.map((item, idx) => `<li id=auto_complete__list_item__${idx} value="${item.name}" onclick="AutoComplete.touch_list(event, '#${this.$target.id}', '#${this.$id}')">${item.name}</li>`).join("")}`;
        }
        this.$list = arr;
    }
    
    this.is_closed = function() {
        var _el = document.querySelector(`#${this.$id}`);
        return _el.style.visibility == "hidden";
    }

    this.close_list = function() {
        var _el = document.querySelector(`#${this.$id}`);
        _el.style.visibility = "hidden";
    }

    this.open_list = function() {
        var _el = document.querySelector(`#${this.$id}`);
        _el.style.visibility = "";
    }

    // ---------------- SELECT LIST -------------------
    this.select_prev = function() {
        this.$selected_item -= 1;
        if(this.$selected_item < -1) {
            this.$selected_item = 0;
        }
        this.select_list(this.$selected_item);
    }

    this.select_next = function() {
        this.$selected_item += 1;
        if(this.$selected_item >= this.$list.length) {
            this.$selected_item = this.$list.length-1;
        }
        this.select_list(this.$selected_item);
    }

    this.select_list = function(ith) {
        this.clear_select();
        var li_el = document.querySelector(`#${this.$id} li#auto_complete__list_item__${ith}`);
        li_el.style.backgroundColor = "#ebebeb";
    }

    this.clear_select = function() {
        var li_el = document.querySelectorAll(`#${this.$id} ul.auto_complete__list li`);
        for(var i = 0; i < li_el.length; i += 1) {
            li_el[i].style.backgroundColor = "";
        }
    }

    /* Constructor */
    if($target != null) {
        this.init();
        this.render();
        this.bind();
    }
}

/* -------------------------------------------------------------------------- */
/*                                    MAIN                                    */
/* -------------------------------------------------------------------------- */
window.g_ac_loaded = false;
window.addEventListener("load", function() {
    if(g_ac_loaded) return;
    console.log("ac loaded");
    window.g_ac_loaded = true;
    switch(location.pathname) {
        case "/h2_system/patch_active/sales/dashboard.html": // 생성된 대시보드
        case "/h2_system/patch_active/sales/dashboard.php": // 대시보드
            new AutoComplete(document.querySelector("#search_query"), {cmd: "002", type:"*"});
        break;
        case "/h2_system/patch_active/sales/salesInsert.php": // 거래명세서 입력
            new AutoComplete(document.querySelector("#vName"),   {cmd: "001", type: "V"}); // 납품처
            new AutoComplete(document.querySelector("#cName"),   {cmd: "001", type: "C"}); // 거래처
            new AutoComplete(document.querySelector("#cbizName"),{cmd: "001", type: "B"}); // 거래처담당자
            new AutoComplete(document.querySelector("#bizName"), {cmd: "003", type: "B"}); // 담당자명
        break;
        case "/h2_system/patch_active/sales/salesUpdate.php": // 거래명세서 수정
        case "/h2_system/patch_active/sales/salesSearch.php": // 거래명세서 검색
            new AutoComplete(document.querySelector("#saleId"), {cmd: "001", type: "S"} ); // 판매번호
            new AutoComplete(document.querySelector("#vId"),    {cmd: "001", type: "V"} ); // 납품처
            new AutoComplete(document.querySelector("#verdorName"),    {cmd: "001", type: "V"} ); // 납품처
            new AutoComplete(document.querySelector("#cId"),    {cmd: "001", type: "C"} ); // 거래처
            new AutoComplete(document.querySelector("#cbizId"), {cmd: "001", type: "B"} ); // 거래처담당자
            new AutoComplete(document.querySelector("#bizId"),  {cmd: "003", type: "B"} ); // 담당자명
            // new AutoComplete(document.querySelector("#SN"),     {cmd: "001", type: "Q"} ); // SN
        break;
        case "/h2_system/patch_active/sales/licenseInsert.php": // 라이센스 입력
            new AutoComplete(document.querySelector("#saleId"), {cmd: "001", type: "S"} ); // 명세서번호
            new AutoComplete(document.querySelector("#SN"),     {cmd: "001", type: "D"} ); // 시리얼번호
            // >>>>>> 230920 명세서에 적힌 보증기간 가져오기
            var sale_id_el = document.querySelector("#saleId");
            var sn_el = document.querySelector("#SN");
            var warranty_el = document.querySelector("#warranty");
            sale_id_el.onblur = function(e) {
              if(sale_id_el.value != "") { // 명세서번호 입력이 있을 경우, 
                setTimeout(function() { 
                  var qs = "cmd=004&val1="+sale_id_el.value;
                  AutoComplete.send_xhr(qs, function(json) { // cmd:004로 AJAX 를 실행 후, 응답값을 각 필드에 채워넣기.
                        if(json.length > 0) {
                          warranty_el.value = json[0].WARRANTY;
                          sn_el.value = json[0].SN;
                        }
                  });
                }, 500);
              }
            };
            // <<<<<< 230920 명세서에 적힌 보증기간 가져오기 
        break;
        case "/h2_system/patch_active/sales/licenseSearch.php": // 라이센스 검색
            new AutoComplete(document.querySelector("#saleId"), {cmd: "001", type: "S"} ); // 명세서번호
            new AutoComplete(document.querySelector("#SN"),     {cmd: "001", type: "D"} ); // 시리얼번호
            new AutoComplete(document.querySelector("#vendorName"),     {cmd: "001", type: "V"} ); // 납품처
        break;
        case "/h2_system/patch_active/sales/deviceSearch.php": // 장비 검색 페이지
            new AutoComplete(document.querySelector("#SN"), {cmd: "001", type: "D"}); // SN
            new AutoComplete(document.querySelector("#orderNo"), {cmd: "002", type: "O"}); // 주문번호
        break;
    }
});