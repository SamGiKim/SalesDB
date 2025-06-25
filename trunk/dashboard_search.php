<?php
//echo "PHP code is executed! TOP"; (OK)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "sales_db.php";

mysqli_set_charset($dbconnect, "utf8");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Document</title>
    <style>
    .height{
        height: 10vh;
    }
    .form{
        position: relative;
    }
    .form .fa-search{
        position: absolute;
        top:20px;
        left: 20px;
        color: #9ca3af;
    }
    .form span{
        position: absolute;
        right: 17px;
        top: 13px;
        padding: 2px;
        border-left: 1px solid #d1d5db;
    }
    .left-pan{
        padding-left: 7px;
    }
    .left-pan i{
        padding-left: 10px;
    }
    .form-input{
        height: 50px;
        text-indent: 33px;
        border-radius: 10px;
        width:80%
    }
    .form-input:focus {
        box-shadow: 0 0 10px #BDCDD6; /*가로, 세로, 퍼진정도*/
        border: 1px solid #ECF2FF;
        background-color:none;
    }
    img{
        width:10px;
        height: auto;
    }
    .form {
        display: flex;
        align-items: center;
    }
    .btn-primary {
        margin-left: 10px; /* 입력 필드와 버튼 사이의 간격 */
        min-width: 60px;  /* 버튼의 최소 너비를 설정합니다. 필요에 따라 값을 조절해보세요. */
        white-space: nowrap;  /* 텍스트 줄바꿈 방지 */
    }

    </style>
</head>
<body>
    <div class="container">
        <div class="row height d-flex justify-content-center align-items-center">
          <div class="col-md-6">
          <form action="dashboard_search_result.php" method="post">
            <div class="form">
                <!-- <form hx-post="dashboard_search_result.php" hx-target="#search-results"> -->
                    <i class="fa fa-search"></i>
                    <input type="text" name="search_query" id="search_query" 
                    class="form-control form-input" placeholder="업체명을 입력해주세요." style="background-color: #fcfafa;">
                    <button type="submit" class="btn btn-primary">검색</button>
                    <span class="left-pan"></span>
                <!-- </form> -->
            </div>  
        </form>
     </div>
    </div>
</div>
</body>
</html>