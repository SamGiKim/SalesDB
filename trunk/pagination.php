<?php
// pagination.php

function pagination($totalItems, $itemsPerPage = 10, $currentPage = 1, $params = [])
{
    $totalPages = max(1, ceil($totalItems / $itemsPerPage));
    $currentPage = max(1, min($currentPage, $totalPages));

    $queryParams = $params;
    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination">';

    // 이전 버튼
    if ($currentPage > 1) {
        $queryParams['page'] = $currentPage - 1;
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($queryParams) . '">&laquo;</a></li>';
    }

    // 페이지 번호
    for ($i = 1; $i <= $totalPages; $i++) {
        $queryParams['page'] = $i;
        $active = ($i == $currentPage) ? 'active' : '';
        echo '<li class="page-item ' . $active . '"><a class="page-link" href="?' . http_build_query($queryParams) . '">' . $i . '</a></li>';
    }

    // 다음 버튼
    if ($currentPage < $totalPages) {
        $queryParams['page'] = $currentPage + 1;
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($queryParams) . '">&raquo;</a></li>';
    }

    echo '</ul>';
    echo '</nav>';
}
?>
