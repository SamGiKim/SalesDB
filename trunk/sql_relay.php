<?php
    switch($_GET["cmd"]){
        case "001": // 자동완성
            $_WORD = '%'.$_GET["val1"].'%';
            $R = get_id_table($_GET["type"]);
            $SQL = "SELECT {$R['_ID']} as id, {$R['_NAME']} as name 
            FROM {$R['_TABLE']} 
            WHERE {$R['_NAME']} LIKE ? LIMIT 10";
        break;
        case "002": // V랑 C를 조인해서 NAME을 SELECT 해서 반환
            $_WORD = '%'.$_GET["val1"].'%';
            $SQL = "SELECT NAME AS name, V_ID AS id
            FROM VENDOR
            WHERE NAME LIKE ?
            UNION
            SELECT NAME AS NAME, C_ID AS id
            FROM CUSTOMER
            WHERE NAME LIKE ?";
            // echo $SQL;
        break;
        case "003": // 엔에스텍 직원만 질의
            $_WORD = '%'.$_GET["val1"].'%';
            $SQL = "SELECT BIZ_ID as id, NAME as name
            FROM BUSINESS
            WHERE C_ID = 'C1' AND NAME LIKE ? LIMIT 10";
        break;
	// >>>>>> 230920 신규 라이센스 등록 응답
	case "004":
	    $SQL = "SELECT WARRANTY, SN
	    FROM SALES
	    WHERE SALE_ID = ?";
	break;
	// <<<<<< 230920 신규 라이센스 등록 응답
        default:
            echo "커맨드가 없습니다.";
            exit;
    }
    // echo $SQL; echo $_WORD;

    $dsn = "mysql:host=127.0.0.1;dbname=salesmng;port=3306;charset=utf8";
    try{
        $db = new PDO($dsn, "root", "password");
        $stmt = $db->prepare($SQL);
        
        switch($_GET["cmd"]) {
            case "001": // 자동완성
            $stmt->execute(array($_WORD));
            break;
            case "002": // 조인질의
            $stmt->execute(array($_WORD, $_WORD));
            break;
            case "003": // 엔에스텍 직원만 질의
            $stmt->execute(array($_WORD));
            break;
	    // >>>>>> 230920 신규 라이센스 등록 응답
	    case "004": // 신규 라이센스 등록 응답
            $stmt->execute(array($_GET["val1"]));
	    break;
	    // <<<<<< 230920 신규 라이센스 등록 응답
        }
        // $stmt->debugDumpParams();
        $row_arr = $stmt->fetchAll();
        echo json_encode($row_arr);
    } catch(PDOException $ex) {
        // $stmt->debugDumpParams();
        // var_dump($ex);
        echo "SQL Error";
        exit;
    }













    // --------------------- FUNCTION SET ----------------------
    function get_id_table($type) {
        $R = array();
        switch($_GET["type"]) {
            case "S": // 거래명세서
                $R['_ID']    = "SALE_ID";
                $R['_TABLE'] = "SALES";
                $R['_NAME'] = "SALE_ID";
            break;
            case "V": // 납품처
                $R['_ID']    = "V_ID";
                $R['_TABLE'] = "VENDOR";
                $R['_NAME'] = "NAME";
            break;
            case "C": // 거래처
                $R['_ID']    = "C_ID";
                $R['_TABLE'] = "CUSTOMER";
                $R['_NAME'] = "NAME";
            break;
            case "B": // 영업
                $R['_ID']    = "BIZ_ID";
                $R['_TABLE'] = "BUSINESS";
                $R['_NAME']  = "NAME";
            break;
            case "D": // SN
                $R['_ID']   = "SN";
                $R["_TABLE"] = "DEVICE";
                $R["_NAME"] = "SN";
        }
        return $R;
    }
?>