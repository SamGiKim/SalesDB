<?php
//echo "PHP code is executed! TOP"; (OK)
//salesSearch.php
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
    <title>거래명세서 검색</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="salesMain.css">
    <script src="https://unpkg.com/htmx.org@1.9.4"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="main">
        <div class="header-container">
            <header>거래명세서 검색</header>
        </div>
        <div class="content">
            <div class="inputBox mx-auto shadow p-5 mt-4">
                <div class="btn-cancel position-relative top-0">
                    <button type="button" class="btn-close" aria-label="Close" onclick="redirectToSalesMain()"></button>
                </div>
                <form 
                    id="searchForm"
                    method="get"
                    hx-get="salesMain.php?action=search"
                    hx-trigger="submit"
                    hx-target=".main"
                    hx-push-url="true">
                        <tr>
                            <td><label for="saleId">판매번호</label></td>
                            <td><input type="text" class="input" name="saleId" id="saleId">
                            <span class="error-message">&nbsp;</span>
                        </td>
                        </tr>
                        <tr>
                            <td><label for="vName">납품처</label></td>
                            <td>
                                <input type="text" class="input" name="vName" id="vName">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr> 
                            <td><label for="cName">거래처</label></td>
                            <td>
                                <input type="text" class="input" name="cName" id="cName">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cbizName">거래처영업</label></td>
                            <td>
                                <input type="text" class="input" name="cbizName" id="cbizName">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="bizName">담당자명</label></td>
                            <td>
                                <input type="text" class="input" name="bizName" id="bizName">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="deliverDate">납품일</label></td>
                            <td>
                                <input type="date" class="input short" name="deliverDate" id="deliverDate">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="sDate">유지보수 시작일</label></td>
                            <td>
                                <input type="date" class="input short" name="sDateFrom" id="sDateFrom">
                                <span>~</span>
                                <input type="date" class="input short" name="sDateTo" id="sDateTo">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dDate">유지보수 종료일</label></td>
                            <td>
                                <input type="date" class="input short" name="dDateFrom" id="dDateFrom">
                                <span>~</span>
                                <input type="date" class="input short" name="dDateTo" id="dDateTo">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="orderNo">주문번호</label></td>
                            <td>
                                <input type="text" class="input" name="orderNo" id="orderNo">
                                <span class="error-message">&nbsp;</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="ref">비고</label></td>
                            <td>
                                <textarea class="txtarea" name="ref" id="ref" rows="2" cols="52"></textarea>
                            </td>
                        </tr>
                    </table>
                    <div class="btn-class">
                        <button type="submit" class="btn btn-primary search wide-btn">검색</button>
                    </div>
                </form>
            </div>
        </div>
            </div>                   
    </div>  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="salesMain.js"></script>
    <script src="/.__/auto_complete.js"></script>
</body>
</html>