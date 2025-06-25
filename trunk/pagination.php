<?php
// pagination.php
function createPagination($totalItems, $itemsPerPage, $currentPage, $urlPattern)
{
    $totalPages = ceil($totalItems / $itemsPerPage);

    echo '<ul class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $url = sprintf($urlPattern, $i);
        echo "<li class='page-item $activeClass'><a href='javascript:void(0);' onclick='goToPage($i);' class='page-link'>$i</a></li>";
    }
    echo '</ul>';
}
?>
