<?php
/** @var array{current_page:int,last_page:int,total:int,per_page:int} $pagination */
/** @var string $baseUrl */
$currentPage = (int) $pagination['current_page'];
$lastPage = (int) $pagination['last_page'];
$total = (int) $pagination['total'];
$perPage = (int) $pagination['per_page'];

$queryParams = $_GET;

if (!function_exists('org_page_url')) {
    function org_page_url(string $baseUrl, array $queryParams, int $page): string
    {
        $queryParams['page'] = $page;
        return $baseUrl . '?' . http_build_query($queryParams);
    }
}

$start = $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
$end = min($currentPage * $perPage, $total);
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3">
    <div class="text-caption">Menampilkan <?= $start ?>-<?= $end ?> dari <?= $total ?> data</div>
    <?php if ($lastPage > 1): ?>
    <nav aria-label="Pagination">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(org_page_url($baseUrl, $queryParams, max(1, $currentPage - 1))) ?>">Sebelumnya</a>
            </li>
            <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="<?= e(org_page_url($baseUrl, $queryParams, $i)) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(org_page_url($baseUrl, $queryParams, min($lastPage, $currentPage + 1))) ?>">Berikutnya</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>
