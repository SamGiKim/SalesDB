# 프로젝트 파일 구조
/home/nstek/h2_system/patch_active/sales/
├── config
│ ├── sales_db.php: 판매 관련 데이터베이스 기능 처리
│ ├── auth.php: 사용자 인증 처리
│ ├── logout.php: 사용자 로그아웃 처리
│ ├── navbar.php: 네비게이션 바 구성
│ └── refreshData.php: 데이터 새로 고침
├── 대시보드 페이지
│ ├── index.php: 웹 애플리케이션 시작 페이지(dashboard.php 호출)
│ ├── dashboard.php: 대시보드 메인 페이지 구성
│ ├── dashboard_search.php: 대시보드 검색 기능 구현
│ └── dashboard_search_result.php: 대시보드 검색 결과 표시
├── 거래명세서 페이지
│ ├── salesMain.php: 판매 관리 메인 페이지
│ ├── salesInsert.php: 새로운 판매 정보 삽입
│ ├── salesDelete.php: 판매 정보 삭제
│ ├── salesSearch.php: 판매 검색 기능 제공
│ └── salesUpdate.php: 판매 정보 업데이트
├── 라이센스 페이지
│ ├── licenseMain.php: 라이센스 관리 메인 페이지
│ ├── licenseInsert.php: 새로운 라이센스 삽입
│ ├── licenseDelete.php: 라이센스 정보 삭제
│ ├── licenseUpdate.php: 라이센스 정보 업데이트
│ ├── licenseSearch.php: 라이센스 검색 기능 제공
│ ├── licenseHistory.php: 라이센스 변경 이력 관리
│ ├── licenseInsertViaDevice.php: 장치를 통한 라이센스 삽입
│ └── licenseRenewal.php: 라이센스 갱신 처리
├── 장비 페이지
│ ├── deviceMain.php: 장비 관리 메인 페이지 구성
│ ├── deviceInsert.php: 장비 데이터베이스 삽입
│ ├── deviceSearch.php: 장비 검색 기능 제공
│ ├── deviceUpdate.php: 장비 정보 업데이트
│ ├── deviceLFSF.php: 장비 관련 특정 기능 처리
│ └── getWarranty.php: 장비 보증 정보 조회
├── JavaScript 파일
│ └── salesMain.js: 전체 페이지와 작용하는 하나의 JavaScript 파일
├── CSS 파일
│ └── salesMain.css: 판매 관리 스타일 정의
└── /.__/
├── auto_complete.html: 자동 완성 기능 테스트
├── auto_complete.js: 자동 완성 기능
└── sql_relay.php: SQL 중계 기능 제공


# 상세 설명
## /home/nstek/h2_system/patch_active/sales/

### config 기능
- **sales_db.php**: 판매 관련 데이터베이스 기능 처리
- **auth.php**: 사용자 인증 처리
- **logout.php**: 사용자 로그아웃 처리
- **navbar.php**: 네비게이션 바 구성
- **refreshData.php**: 데이터 새로 고침

### 대시보드 페이지
- **index.php**: 웹 애플리케이션 시작 페이지(dashboard.php 호출)
- **dashboard.php**: 대시보드 메인 페이지 구성
  - 사용자 인증 및 데이터베이스 연결을 통해 다양한 통계와 정보를 제공
  - 총 고객 수, 장비 수, 진행 중인 유지보수 건수, 보증기간 종료 예정 및 만료 건수 등을 표시
  - EOS(End of Service) 정보를 계산하여 표시
  - "갱신" 버튼을 통해 실시간 데이터로 업데이트 가능
- **dashboard_search.php**: 대시보드 검색 기능 구현
  - 검색어 입력 폼을 제공하며, 입력된 검색어를 기반으로 데이터를 필터링
  - `dashboard_search_result.php`로 데이터를 전송하여 검색 결과를 처리
  - Bootstrap과 Font Awesome을 사용하여 검색 폼 스타일링
- **dashboard_search_result.php**: 대시보드 검색 결과 표시
  - `dashboard_search.php`에서 전송된 검색어를 기반으로 데이터베이스에서 검색 결과를 가져와 표시
  - 검색 결과를 테이블이나 리스트 형태로 제공
  - 검색 쿼리 실행 중 에러 발생 시 에러 메시지 표시


### 거래명세서 페이지
- **salesMain.php**: 판매 관리 메인 페이지
  - 사용자 권한에 따라 가격 정보 표시를 제한 (관리자 및 내부 네트워크 사용자만 가격 정보 확인 가능)
  - 장비의 SN을 클릭하면 관련 페이지로 이동
  - SN 리스트가 없는 경우 주문번호를 빨간색으로 표시하여 시각적 경고 제공
  - 각 판매 항목에 대한 세부 정보를 아코디언 형식으로 확장하여 표시
- **salesInsert.php**: 새로운 판매 정보 삽입
- **salesDelete.php**: 판매 정보 삭제
- **salesSearch.php**: 판매 검색 기능 제공
- **salesUpdate.php**: 판매 정보 업데이트


### 라이센스 페이지
- **licenseMain.php**: 라이센스 관리 메인 페이지
  - 다양한 조건에 따라 라이센스 데이터를 검색하고 필터링
  - 유상 및 무상 보증기간의 종료 예정 및 만료 상태를 구분하여 표시
  - 각 라이센스 항목에 대한 세부 정보를 아코디언 형식으로 확장하여 표시
  - SN을 클릭하면 관련 페이지로 이동
  - 라이센스 히스토리를 조회하여 갱신 횟수 및 세부 정보를 제공
- **licenseInsert.php**: 새로운 라이센스 삽입
- **licenseDelete.php**: 라이센스 정보 삭제
- **licenseUpdate.php**: 라이센스 정보 업데이트
- **licenseSearch.php**: 라이센스 검색 기능 제공
- **licenseHistory.php**: 라이센스 변경 이력 관리
  - 특정 SALE_ID와 SN에 대한 라이센스 변경 이력을 조회
  - LICENSE_HISTORY 테이블에서 SALE_ID와 SN을 기준으로 모든 이력 데이터를 가져옴
  - 각 이력 항목은 TYPE, PRICE, S_DATE, D_DATE, REF, WARRANTY, INSPECTION, SUPPORT, LICENSE_INSERTED_DATE 등의 정보를 포함
  - 관련 테이블: LICENSE_HISTORY
  - 이 테이블은 라이센스의 변경 이력을 저장하며, SALE_ID, SN, NO가 기본 키
- **licenseInsertViaDevice.php**: 장치를 통한 라이센스 삽입
  - 장비의 시리얼 번호(SN)를 통해 라이센스를 삽입
  - 데이터베이스 상호작용:
    - DEVICE 테이블에서 SN을 사용하여 SALES 테이블과 조인하여 SALE_ID와 WARRANTY를 가져옴
    - SALES 테이블에서 SALE_ID를 기준으로 WARRANTY, TOT_PRICE, S_DATE, D_DATE를 조회
  - 라이센스 정보를 입력받아 LICENSE 테이블에 새로운 레코드를 삽입
  - 관련 테이블: DEVICE, SALES, LICENSE
    - LICENSE 테이블은 현재 유효한 라이센스 정보를 저장
- **licenseRenewal.php**: 라이센스 갱신 처리
  - 기존 라이센스를 갱신하고, 갱신 전의 데이터를 LICENSE_HISTORY에 저장
  - 데이터베이스 상호작용:
    - LICENSE_HISTORY 테이블에 기존 라이센스 데이터를 삽입하여 이력을 기록
    - LICENSE 테이블에서 기존 라이센스를 삭제하고, 새로운 라이센스 정보를 삽입
    - 갱신 시 SALE_ID와 SN을 기준으로 중복 데이터가 없는지 확인
    - 갱신 시작일(S_DATE)이 이전 종료일(D_DATE)보다 이후인지 확인하여 유효성을 검사
  - 관련 테이블: LICENSE, LICENSE_HISTORY
    - LICENSE 테이블은 현재 유효한 라이센스 정보를 저장
    - LICENSE_HISTORY 테이블은 라이센스의 변경 이력을 저장

  ### 장비 페이지
- **deviceMain.php**: 장비 관리 메인 페이지 구성
  - 장비 관리 메인 페이지를 구성하여 장비의 정보를 관리
  - 데이터베이스 상호작용:
    - DEVICE 테이블에서 장비의 정보를 조회하여 사용자에게 표시
    - 장비의 시리얼 번호(SN)를 통해 관련 정보를 검색하고, 필요에 따라 장비 정보를 업데이트할 수 있는 기능을 제공
  - 관련 테이블: DEVICE
    - DEVICE 테이블은 장비의 정보를 저장하며, SN이 기본 키
- **deviceInsert.php**: 장비 데이터베이스 삽입
- **deviceSearch.php**: 장비 검색 기능 제공
- **deviceUpdate.php**: 장비 정보 업데이트
- **deviceLFSF.php**: 장비 관련 특정 기능 처리
  - 데이터베이스 상호작용:
    - DEVICE 테이블과 상호작용하여 장비의 특정 속성을 업데이트하거나 조회
    - 장비의 LF(License Factor) 및 SF(Sales Factor)와 관련된 데이터를 처리
  - 관련 테이블: DEVICE
- **getWarranty.php**: 장비 보증 정보 조회
  - 장비의 보증 정보를 조회하여 사용자에게 제공
  - 데이터베이스 상호작용:
    - DEVICE 테이블에서 장비의 시리얼 번호(SN)를 기준으로 보증 기간(WARRANTY)을 조회

## JavaScript 파일
- **salesMain.js**: 전체 페이지와 작용하는 하나의 JavaScript 파일


## CSS 파일
- **salesMain.css**: 판매 관리 스타일 정의


## /home/nstek/h2_system/patch_active/sales/.__/
- **auto_complete.html**: 자동 완성 기능 테스트
- **auto_complete.js**: 자동 완성 기능
- **sql_relay.php**: SQL 중계 기능 제공





