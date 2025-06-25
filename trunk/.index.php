<?php
if($_GET["q"]) {

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Viewer</title>
    <style>
        body { background-color: #202123; color: #EBEBEB; }
        /* body { background-color: #193718; color: #FF8BFF; } */
        .l_side, .r_side, .splitter {
            position: absolute; height: 100%; overflow: hidden;
        }
        .splitter {
            width: 3px; z-index: 100; cursor: col-resize;
        }
        .l_side > div, .r_side > div {
            position: relative; height: 100%; width: 100%; 
        }
        .l_content, .r_content {
            margin: auto; top: 50%; transform: translateY(-50%);
            position: absolute; width: 100%;
        }
        /* .l_content { background-color: yellow; }
        .r_content { background-color: green; } */
        .header {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 10px; grid-auto-rows: minmax(100px, auto);
        }
        .keyword_input {
            margin-left:10%; width:calc(80% - 50px); padding-left: 20px; padding-right: 20px;
            height: 50px;  font-size: 18px;
            background-color: #40414F; border: none;
            border-radius: 10px; outline: none; 
            caret-color: white; color: white; 
        }
        .issue_input { 
            margin-left:10%; width:80%; padding-left: 20px; padding-right: 20px;
            height: 50px;  font-size: 18px;
            background-color: #40414F; border: none;
            border-radius: 10px; outline: none; 
            caret-color: white; color: white; 
        }
        textarea#keyword_result {
            margin-top: 50px; width: calc(100% - 80px); padding: 20px;
            background-color: #40414F; border: none; border-radius: 10px; outline: none; 
            caret-color: white; color: white; resize: none;
        }
        textarea.chat {
            margin-top: 50px; width: calc(100% - 80px); padding: 20px;
            height: calc(100vh - 500px); font-size: 20px;
            background-color: #40414F; border: none;
            border-radius: 10px; outline: none; 
            caret-color: white; color: white; resize: none;
        }
        #ref { width: 50px; height: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="l_side">
        <input class="keyword_input" type="text" placeholder="키워드 검색"/>
        <button onclick="keyword_input_new_window()">새창</button>
        </div>
        <div class="r_side">
        <input class="issue_input" type="number" min="0" value="<?=$_GET['q']?>" placeholder="질문ID"/>
        </div>
    </div>
    <div class="container">
        <div class="l_side">
            <div class="l_content">
                <center>
                <h2>Question</h2>
                <textarea class="chat" id="question" cols="80" rows="100"></textarea>
                </center>
            </div>
        </div>
        <div class="r_side">
            <div class="r_content">
                <center>
                <h2>Answer</h2>
                <textarea class="chat" id="answer" cols="80" rows="50"></textarea>
                </center>
                <a id="ref_link" href="" target="_blank"><img id="ref" src=""></img></a>
            </div>
        </div>
    </div>
</body>
<script>
    
    var g_el = {
        keyword_input: document.querySelector(".keyword_input"),
        issue_input: document.querySelector(".issue_input"),
        question: document.querySelector("#question"),
        answer: document.querySelector("#answer"),
        ref: document.querySelector("#ref"),
        ref_link: document.querySelector("#ref_link"),
    };

    window.onload = function() {
        Isend_qstn_issue(<?=$_GET['q']?>);
    }

    g_el.keyword_input.addEventListener("keydown", keyword_input_keydown_handler);
    g_el.issue_input.addEventListener("keydown", issue_input_keydown_handler);
    function keyword_input_keydown_handler(e) {
        console.log("keyword_input_keydown_handler", e);
        switch(e.keyCode) {
            case 13: //ENTER
                Isend_keyword_issue(g_el.keyword_input.value);
            break;
        }
    }
    function keyword_input_new_window() {
        Isend_keyword_issue(g_el.keyword_input.value, function(d) {
            console.log("d", d);
            window.open("/QnA/?q="+d.qstn_id, "_blank", "Popup", d.qstn_id, "width=800,height=600");
        });
        
    }
    function issue_input_keydown_handler(e) {
        console.log("issue_input_keydown_handler", e);
        var qstn_id;
        switch(e.keyCode) {
            case 13: //ENTER
                qstn_id = g_el.issue_input.value;
                Isend_qstn_issue(qstn_id);
            break;
            case 38: //ArrowUp
                qstn_id = g_el.issue_input.value;
                qstn_id++;
                Isend_qstn_issue(qstn_id);
            break;
            case 40: //ArrowDown
                qstn_id = g_el.issue_input.value;
                qstn_id--;
                Isend_qstn_issue(qstn_id);
            break;
        }
    }

    function Isend_keyword_issue(keyword, callback) {
        var xhr_keyword = new XMLHttpRequest();
        var sql_keyword = `SELECT qstn_id FROM QnA.question_table WHERE question LIKE '%${keyword}%'`;
        xhr_keyword.open("GET", "./sql_relay.php?sql="+sql_keyword, true);
        xhr_keyword.onreadystatechange = function() { 
            xhr_handler_default(this, function(json) {
                console.log("json", json);
                if(!callback) {
                    g_el.issue_input.value = json[0].qstn_id;
                    Isend_qstn_issue(g_el.issue_input.value);
                } else {
                    for(var i = 0; i < json.length; i += 1) {
                        callback(json[i]);
                    }
                }
                
            }); 
        };
        xhr_keyword.send();
    }

    function Isend_qstn_issue(qstn_id) {
        if(qstn_id == undefined || qstn_id == "") return;
        var xhr_qstn = new XMLHttpRequest();
        var sql_qstn = `SELECT * FROM QnA.question_table WHERE qstn_id in (${qstn_id});`;
        xhr_qstn.open("GET", "./sql_relay.php?sql="+sql_qstn, true);
        xhr_qstn.onreadystatechange = function() { 
            g_el.question.innerText = "";
            
            xhr_handler_foreach(this, function(d) {
                // d.question = d.question.replaceAll("\r\n", "&#10;");
                // d.question = d.question.replaceAll(/(\n|\r\n)/g, "<br>");
                g_el.question.innerText += `[${d.time}] <${d.who}> / ${d.context} : ${d.question}\n\n`;
            }); 
        };
        xhr_qstn.send();
        
        var xhr_ans = new XMLHttpRequest();
        var sql_ans = `SELECT * FROM QnA.answer_table WHERE ans_id in (${qstn_id});`;
        xhr_ans.open("GET", "./sql_relay.php?sql="+sql_ans, true);
        xhr_ans.onreadystatechange = function() { 
            g_el.answer.innerText = "";
            xhr_handler_foreach(this, function(d) {
                g_el.answer.innerText += `[${d.time}] <${d.who}> : ${d.answer}\n\n`;
                if(g_el.ref) {
                    g_el.ref.src = `${d.ref}`;
                    g_el.ref_link.href = `${d.ref}`;
                }
                
            }); 
        };
        xhr_ans.send();
    }

    function xhr_handler_foreach(xhr, callback) {
        if(xhr.readyState === 4) {
            if(xhr.status === 200) {
                var r_txt = xhr.responseText;
                var json = JSON.parse(r_txt);                
                for(var i = 0; i < json.length; i += 1) {
                    var d = json[i];
                    callback(d);
                }
            }
        }
    }
    function xhr_handler_default(xhr, callback) {
        if(xhr.readyState === 4) {
            if(xhr.status === 200) {
                var r_txt = xhr.responseText;
                var json = JSON.parse(r_txt);                
                callback(json);
            }
        }
    }
</script>
<script>
        var container_el = document.querySelector(".container");
        var l_side_el = document.querySelector(".l_side");
        var r_side_el = document.querySelector(".r_side");
        var l_content_el = document.querySelector(".l_content");
        var r_content_el = document.querySelector(".r_content");
        var sb = new SplitterBar(container_el, l_side_el, r_side_el, l_content_el, r_content_el);

        function SplitterBar(container, l_side, r_side, l_content, r_content) {
            // >>> INIT SPLIT FRAME
            var splitter   = document.createElement("div");
            splitter.classList.add("splitter");
            // <<< INIT SPLIT FRAME

            if(l_content !== null) l_side.appendChild(l_content);
            if(r_content !== null) r_side.appendChild(r_content);
            container.appendChild(splitter);
            
            // >>> INIT SPLIT DEFAULT SIZE
            splitter.style.width = "10px;"
            splitter.style.left = "50%";
            splitter.style.transform = "translateX(-50%)";
            splitter.style.background = "grey";
            l_side.style.left = 0;
            l_side.style.width = (splitter.offsetLeft - splitter.offsetWidth/2) + 'px';
            r_side.style.left = (splitter.offsetLeft + splitter.offsetWidth/2) + 'px';
            r_side.style.width = (container.offsetWidth - splitter.offsetLeft - 10) + 'px';
            // <<< INIT SPLIT DEFAULT SIZE

            container.appendChild(l_side);
            container.appendChild(r_side);

            // >>> LOCAL VARIABLE
            var mouse_is_down = false;
            var start_x = null; var start_y = null;
            var global_x_coordinate = null;
            // <<< LOCAL VARIABLE

            splitter.addEventListener("mousedown",  splitter_mousedown_handler);
            splitter.addEventListener("mousemove",  sth_mousemove_handler);
            l_side.addEventListener("mousemove",    sth_mousemove_handler);
            r_side.addEventListener("mousemove",    sth_mousemove_handler);
            document.body.addEventListener("mouseup",   body_mouseup_handler);
            document.addEventListener("mouseup",        body_mouseup_handler);
            document.addEventListener("mousemove",      body_mousemove_handler);

            function body_mouseup_handler(e) { mouse_is_down = false; }
            function body_mousemove_handler(e) {
                e.preventDefault();
                e.stopPropagation();
                // console.log("body_mousemove_handler");
                var container_width = container.getBoundingClientRect().width;
                var hovering_on_document = e.target.nodeName == "HTML" || e.target.nodeName == "BODY";
                var doc_x = e.offsetX - container.getBoundingClientRect().x - start_x;
                if(mouse_is_down) {
                    if(hovering_on_document) {
                        if(doc_x < 0) { doc_x = 0; }
                        if(doc_x + splitter.offsetWidth > container.offsetWidth) {
                            doc_x = container_width - splitter.offsetWidth;
                        }

                        splitter.style.left = doc_x + "px";
                        l_side.style.width = splitter.offsetLeft - splitter.offsetWidth/2 + "px";
                        r_side.style.width = (container.offsetWidth - l_side.offsetWidth - splitter.offsetWidth) + "px";
                        r_side.style.left = splitter.offsetLeft + (splitter.offsetWidth/2) + "px";
                        // console.log("if / l_side.style.width", l_side.style.width);
                    } else {
                        if(global_x_coordinate + splitter.offsetWidth > container_width) {
                            global_x_coordinate = container_width - splitter.offsetWidth;
                        }
                        if(global_x_coordinate < 0) {
                            global_x_coordinate = 0;
                        }

                        splitter.style.left = global_x_coordinate + "px";
                        l_side.style.width = splitter.offsetLeft - splitter.offsetWidth/2 + "px";
                        r_side.style.width = (container.offsetWidth - l_side.offsetWidth - splitter.offsetWidth) + "px";
                        r_side.style.left = splitter.offsetLeft + splitter.offsetWidth / 2 + "px";
                        // console.log("else / l_side.style.width", l_side.style.width);
                    }
                }
            }
            function splitter_mousedown_handler(e) {
                e.preventDefault();
                // console.log("splitter_mousedown_handler");
                mouse_is_down = true;
                start_x = e.offsetX;
                start_y = e.offsetY;
            }
            function sth_mousemove_handler(e) {
                e.preventDefault();
                // console.log("sth_mousemove_handler", this.offsetLeft, e.offsetX, start_x);
                var left = this.offsetLeft;
                global_x_coordinate = left + e.offsetX - start_x;
            }
        }
    </script>
</html>
