var el = document.createElement("script"); el.src = ".__/auto_complete.js"; document.body.appendChild(el);

/* -------------------------------------------------------------------------- */
/*                                salesMain 페이지                             */
/* -------------------------------------------------------------------------- */
//네비게이션
document.addEventListener('DOMContentLoaded', function () {
    var navItems = document.querySelectorAll('.nav-item a');
    navItems.forEach(function (item) {
        item.addEventListener('click', function () {
            navItems.forEach(function (innerItem) {
                innerItem.classList.remove('active');
            });
            item.classList.add('active');
        });
    });
});

//'신규'버튼
function goToSalesInsert() {
    window.location.href = "salesInsert.php";
}
//'검색'버튼
function goToSalesSearch() {
    window.location.href = "salesSearch.php";
}
//salesInsert에서 취소버튼 눌렀을때 
function redirectToSalesMain() {
    window.location.href = 'salesMain.php';
}

/* -------------------------------------------------------------------------- */
/*                                  pagination                                */
/* -------------------------------------------------------------------------- */

// function goToPage(pageNumber) {
//     console.log("요청된 페이지 번호:", pageNumber);

//     $.ajax({
//         url: 'salesMain.php',
//         type: 'GET',
//         dataType: 'json', // JSON 응답을 자동으로 처리함
//         data: { page: pageNumber },
//         beforeSend: function () {
//             console.log("AJAX 요청 전송 중...");
//         },
//         success: function (response) {
//             console.log("AJAX 요청 성공!", response);

//             // 테이블 내용 업데이트
//             let tableContent = '';
//             response.data.forEach(row => {
//                 tableContent += `<tr>
//                     <td class="col-1">${row.SALE_ID || ''}</td>
//                     <td class="col-1">${row.V_NAME || ''}</td>
//                     <td class="col-1">${row.C_NAME || ''}</td>
//                     <td class="col-1">${row.CBIZ_NAME || ''}</td>
//                     <td class="col-1">${row.BIZ_NAME || ''}</td>
//                     <td class="col-2" style="text-align: right; padding-right: 5%;">${row.TOT_PRICE ? Number(row.TOT_PRICE).toLocaleString() + '원' : ''}</td>
//                     <td class="col-1">${row.DELIVER_DATE || ''}</td>
//                     <td class="col-1">${row.S_DATE || ''}</td>
//                     <td class="col-1">${row.D_DATE || ''}</td>
//                     <td class="col-1" style="color: ${row.DEVICE_SN ? 'inherit' : 'red'};">${row.ORDER_NO || ''}</td>
//                     <td class="col-1">${row.WARRANTY ? row.WARRANTY + '개월' : ''}</td>
//                 </tr>`;
//             });
//             document.querySelector('.table.main-tbl tbody').innerHTML = tableContent;
//             console.log("페이지 내용 업데이트 완료.");

//             // 페이지네이션 스타일 업데이트
//             updatePaginationStyle(pageNumber);
//         },
//         error: function (xhr, status, error) {
//             console.error("AJAX 요청 실패:", status, error);
//             console.error("서버 응답:", xhr.responseText);  // 서버에서 반환된 에러 메시지 출력
//         }
//     });
// }

// function updatePaginationStyle(currentPage) {
//     $('.page-item').removeClass('active'); // 모든 페이지에서 active 제거
//     $(`.pagination-list .page-item[data-page="${currentPage}"]`).addClass('active'); // data-page로 활성화
// }

/* -------------------------------------------------------------------------- */
/*                                 salesSearch                                */
/* -------------------------------------------------------------------------- */
// salesMain.js
function validateForm() {
    const saleId = document.getElementById("saleId").value;
    const vId = document.getElementById("vId").value;
    const cId = document.getElementById("cId").value;
    const cbizId = document.getElementById("cbizId").value;
    const bizId = document.getElementById("bizId").value;
    const deliverDate = document.getElementById("deliverDate").value;
    const sDate = document.getElementById("sDate").value;
    const dDate = document.getElementById("dDate").value;
    const orderNo = document.getElementById("orderNo").value;
    const SN = document.getElementById("SN").value;

    if (!saleId && !vId && !cId && !cbizId && !bizId && !deliverDate && !sDate && !dDate && !orderNo && !SN) {
        alert("한 개 이상의 값을 입력하세요");
        return false;
    }

    return true;
}

/* -------------------------------------------------------------------------- */
/*                               licenseMain 페이지                           */
/* --------------------------------------------------------------------------*/
//'신규'버튼
function goToLicenseInsert() {
    window.location.href = "licenseInsert.php";
}
//'검색'버튼
function goToLicenseSearch() {
    window.location.href = "licenseSearch.php";
}

/* -------------------------------------------------------------------------- */
/*                              licenseInsert                                 */
/* -------------------------------------------------------------------------- */
function redirectToLicenseMain() {
    window.location.href = "licenseMain.php";
}


/* -------------------------------------------------------------------------- */
/*                          licenseInsert 페이지       유효성검사     --> licenseInsert.php에 코드 직접 삽입                 */
/* -------------------------------------------------------------------------- */
// document.addEventListener('DOMContentLoaded', function () {
//     debugger;
//     console.log("licenseInsert JS 도달!");

//     document.getElementById('lcsInsertForm').addEventListener('submit', function (event) {
//         console.log("licenseInsert form 제출");
//         event.preventDefault(); // 유효성 검사 후 조건 만족할 때만 폼 제출

//         let SN = document.getElementById('SN').value;
//         console.log('SN 값 : ', SN);
//         let saleId = document.getElementById('saleId').value;
//         console.log('saleId 값 : ', saleId);
//         let price = document.getElementById('price').value;
//         let type = document.getElementById('type').value;
//         let errors = [];

//         // 에러 메시지 초기화
//         document.getElementById('error-saleId').textContent = '';
//         document.getElementById('error-SN').textContent = '';
//         document.getElementById('error-type').textContent = '';
//         document.getElementById('error-price').textContent = '';

//         if (!saleId) {
//             console.log('saleId가 없음!');
//             errors['saleId'] = "명세서번호(SALE_ID)를 입력해주세요.";
//             document.getElementById('error-saleId').textContent = errors['saleId'];
//         }

//         if (type !== "무상" && !SN) {
//             errors['SN'] = "유상일 경우 시리얼번호(SN)를 입력해주세요.";
//             document.getElementById('error-SN').textContent = errors['SN'];
//         }

//         if (type === "유상" && price === "") {
//             errors['price'] = "유상인 경우 금액을 입력해주세요.";
//             document.getElementById('error-price').textContent = errors['price'];
//         }

//         if (Object.keys(errors).length) {
//             console.log('에러 발생!', errors);
//         } else {
//             console.log("에러 없음");
//             this.submit();
//         }
//     });

// });

/* -------------------------------------------------------------------------- */
/*                                licenseSearch                               */
/* -------------------------------------------------------------------------- */
// addFilter 함수를 전역 스코프로 이동
function addFilter(filterValue) {
    let filterButton = document.querySelector('.btn-filter[value="' + filterValue + '"]');

    // 버튼의 활성화 상태를 확인하고 변경
    if (filterButton.classList.contains('filter-active')) {
        filterButton.classList.remove('filter-active');

        // 만약 활성화 상태가 아니면 hidden input을 제거
        let existingInput = document.getElementById(filterValue);
        if (existingInput) {
            existingInput.remove();
        }
    } else {
        filterButton.classList.add('filter-active');

        // hidden input이 존재하지 않는다면 생성하여 추가
        if (!document.getElementById(filterValue)) {
            let inputElem = document.createElement('input');
            inputElem.setAttribute('type', 'hidden');
            inputElem.setAttribute('name', 'filters[]');
            inputElem.setAttribute('value', filterValue);
            inputElem.setAttribute('id', filterValue);
            document.getElementById('lcsSearchForm').appendChild(inputElem);
        }
    }
}

/* -------------------------------------------------------------------------- */
/*                            LICENSEINSERT-WARRANTY                          */
/* -------------------------------------------------------------------------- */
function fetchWarranty(saleId, SN) {
    $.ajax({
        url: 'getWarranty.php',
        method: 'POST',
        data: {
            saleId: saleId,
            SN: SN
        },
        success: function (response) {
            // WARRANTY 값을 화면에 표시
            $("#warranty_years").val(response.warranty_years);
            $("#warranty_months").val(response.warranty_months);
        }
    });
}

/* -------------------------------------------------------------------------- */
/*                               deviceMain.php                               */
/* -------------------------------------------------------------------------- */
//'신규'버튼
function goToDeviceInsert() {
    window.location.href = "deviceInsert.php";
}

/* -------------------------------------------------------------------------- */
/*                              deviceUpdate.php                              */
/* -------------------------------------------------------------------------- */
function redirectToDeviceMain() {
    window.location.href = 'deviceMain.php';
}


